<?php

namespace Webkul\PaymentId\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\PaymentId\Payment\Midtrans;

class MidtransController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    public function redirect()
    {
        $cart = Cart::getCart();
        if (! $cart) return redirect()->route('shop.checkout.cart.index');

        $midtrans = app(Midtrans::class);

        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        $snapToken = $this->getSnapToken($midtrans, $order);

        if (! $snapToken) {
            session()->flash('error', 'Gagal terhubung ke Midtrans. Silakan coba lagi.');
            return redirect()->route('shop.checkout.cart.index');
        }

        Cart::deActivateCart();

        return view('paymentid::midtrans-redirect', [
            'snapToken'  => $snapToken,
            'clientKey'  => $midtrans->getClientKey(),
            'isSandbox'  => $midtrans->isSandbox(),
            'order'      => $order,
        ]);
    }

    public function notification(Request $request)
    {
        $midtrans = app(Midtrans::class);
        $serverKey = $midtrans->getServerKey();

        $payload = $request->all();

        $signatureKey = hash('sha512',
            $payload['order_id'] .
            $payload['status_code'] .
            $payload['gross_amount'] .
            $serverKey
        );

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $order = $this->orderRepository->findOneByField('cart_id', $payload['order_id']);

        if (! $order) return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
            $this->handleSuccess($order);
        } elseif ($transactionStatus === 'settlement') {
            $this->handleSuccess($order);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->handleCancel($order);
        } elseif ($transactionStatus === 'pending') {
            $order->update(['status' => 'pending_payment']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function finish(Request $request)
    {
        $order = $this->orderRepository->findOneByField('cart_id', $request->get('order_id'));
        if ($order) {
            return redirect()->route('shop.checkout.success', $order->id);
        }
        return redirect()->route('shop.checkout.cart.index');
    }

    public function unfinish(Request $request)
    {
        return redirect()->route('shop.checkout.cart.index');
    }

    public function error(Request $request)
    {
        session()->flash('error', 'Pembayaran gagal. Silakan coba lagi.');
        return redirect()->route('shop.checkout.cart.index');
    }

    protected function getSnapToken(Midtrans $midtrans, $order): ?string
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $order->cart_id,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_first_name,
                'last_name'  => $order->customer_last_name,
                'email'      => $order->customer_email,
                'phone'      => $order->customer_phone ?? '',
            ],
            'enabled_payments' => $midtrans->getEnabledChannels(),
            'callbacks' => [
                'finish'   => route('paymentid.midtrans.finish'),
                'unfinish' => route('paymentid.midtrans.unfinish'),
                'error'    => route('paymentid.midtrans.error'),
            ],
        ];

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $midtrans->getSnapUrl(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($params),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Basic ' . base64_encode($midtrans->getServerKey() . ':'),
                ],
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($response, true);
            return $result['token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap error: ' . $e->getMessage());
            return null;
        }
    }

    protected function handleSuccess($order): void
    {
        if ($order->status !== 'completed') {
            $order->update(['status' => 'completed']);
            if ($order->canInvoice()) {
                $this->invoiceRepository->create(array_merge($this->orderRepository->prepareInvoiceData($order), ['state' => 'paid']));
            }
        }
    }

    protected function handleCancel($order): void
    {
        if ($order->status !== 'canceled') {
            $order->update(['status' => 'canceled']);
        }
    }
}
