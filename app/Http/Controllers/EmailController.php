<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PDF;

class EmailController extends Controller
{
 public function sendReceiptEmail(Request $request)
{
    // 1. Validate the incoming request
    $request->validate([
        'receipt_html' => 'required',
        'customer_name' => 'required',
        'email' => 'required|email',
    ]);

    $receiptHtml = $request->input('receipt_html');
    $customerName = $request->input('customer_name');
    $email = $request->input('email');

    // 2. Send the email with the HTML preview directly in the body
    Mail::send('emails.receipt', ['htmlContent' => $receiptHtml, 'customerName' => $customerName], function ($message) use ($customerName, $email) {
        $message->to($email)
                ->subject("Invoice from TGK Traders - {$customerName}");
    });

    return response()->json(['success' => true, 'message' => 'Email sent successfully!']);
}
}
