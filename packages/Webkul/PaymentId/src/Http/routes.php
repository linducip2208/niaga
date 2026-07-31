<?php

use Illuminate\Support\Facades\Route;
use Webkul\PaymentId\Http\Controllers\MidtransController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('paymentid/midtrans')->group(function () {
        Route::get('redirect', [MidtransController::class, 'redirect'])->name('paymentid.midtrans.redirect');
        Route::post('notification', [MidtransController::class, 'notification'])->name('paymentid.midtrans.notification');
        Route::get('finish', [MidtransController::class, 'finish'])->name('paymentid.midtrans.finish');
        Route::get('unfinish', [MidtransController::class, 'unfinish'])->name('paymentid.midtrans.unfinish');
        Route::get('error', [MidtransController::class, 'error'])->name('paymentid.midtrans.error');
    });
});
