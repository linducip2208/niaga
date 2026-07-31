<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Midtrans Payment</title>
    <script src="{{ $isSandbox ? 'https://app.sandbox.midtrans.com/snap/snap.js' : 'https://app.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey }}"></script>
</head>
<body style="margin:0;padding:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc;font-family:Arial,sans-serif;">
    <div style="text-align:center;">
        <p style="color:#64748b;font-size:16px;">Mengalihkan ke halaman pembayaran...</p>
        <div style="margin-top:16px;width:40px;height:40px;border:3px solid #e2e8f0;border-top-color:#0ea5e9;border-radius:50%;animation:spin 0.8s linear infinite;margin-left:auto;margin-right:auto;"></div>
    </div>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
    <script>
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) { window.location.href = '{{ route('paymentid.midtrans.finish') }}?order_id={{ $order->cart_id }}&status_code=' + result.status_code; },
            onPending: function(result) { window.location.href = '{{ route('paymentid.midtrans.finish') }}?order_id={{ $order->cart_id }}&status_code=' + result.status_code; },
            onError: function(result) { window.location.href = '{{ route('paymentid.midtrans.error') }}'; },
            onClose: function() { window.location.href = '{{ route('paymentid.midtrans.unfinish') }}'; }
        });
    </script>
</body>
</html>
