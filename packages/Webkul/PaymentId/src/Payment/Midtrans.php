<?php

namespace Webkul\PaymentId\Payment;

use Webkul\Payment\Payment\Payment;

class Midtrans extends Payment
{
    protected $code = 'midtrans';

    protected $supportedChannels = [
        'credit_card', 'bca_va', 'bni_va', 'bri_va', 'mandiri_bill',
        'permata_va', 'cimb_va', 'gopay', 'shopeepay', 'qris',
        'alfamart', 'indomaret', 'akulaku', 'kredivo',
    ];

    public function getRedirectUrl()
    {
        return route('paymentid.midtrans.redirect');
    }

    public function getClientKey()
    {
        return $this->getConfigData('client_key');
    }

    public function getServerKey()
    {
        return $this->getConfigData('server_key');
    }

    public function getMerchantId()
    {
        return $this->getConfigData('merchant_id');
    }

    public function isSandbox(): bool
    {
        return (bool) $this->getConfigData('sandbox');
    }

    public function getSnapUrl(): string
    {
        return $this->isSandbox()
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';
    }

    public function getApiUrl(): string
    {
        return $this->isSandbox()
            ? 'https://api.sandbox.midtrans.com/v2'
            : 'https://api.midtrans.com/v2';
    }

    public function getEnabledChannels(): array
    {
        $channels = $this->getConfigData('enabled_channels');
        return $channels ? explode(',', $channels) : $this->supportedChannels;
    }
}
