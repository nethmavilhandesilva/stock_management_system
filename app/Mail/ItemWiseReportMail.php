<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ItemWiseReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $sales;
    public $total_packs;
    public $total_weight;
    public $total_amount;
    public $settingDate;

    /**
     * Create a new message instance.
     *
     * @param  \Illuminate\Support\Collection  $sales
     * @param  int  $total_packs
     * @param  float  $total_weight
     * @param  float  $total_amount
     * @return void
     */
    public function __construct(Collection $sales, $total_packs, $total_weight, $total_amount)
    {
        $this->sales = $sales;
        $this->total_packs = $total_packs;
        $this->total_weight = $total_weight;
        $this->total_amount = $total_amount;
        $this->settingDate = Setting::value('value');
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📦 අයිතමය අනුව වාර්තාව (Item-Wise Report)',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.item_wise_report',
            with: [
                'sales' => $this->sales,
                'total_packs' => $this->total_packs,
                'total_weight' => $this->total_weight,
                'total_amount' => $this->total_amount,
                'settingDate' => $this->settingDate,
            ],
        );
    }
}
