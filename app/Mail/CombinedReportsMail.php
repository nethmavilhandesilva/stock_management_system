<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CombinedReportsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dayStartReportData;
    public $grnReportData;
    public $salesReportData; // Added for sales report data
    public $dayStartDate;

    /**
     * Create a new message instance.
     *
     * @param mixed $dayStartReportData
     * @param mixed $grnReportData
     * @param mixed $salesReportData
     * @param \Carbon\Carbon $dayStartDate
     * @return void
     */
    public function __construct($dayStartReportData, $grnReportData, $salesReportData, $dayStartDate)
    {
        $this->dayStartReportData = $dayStartReportData;
        $this->grnReportData = $grnReportData;
        $this->salesReportData = $salesReportData;
        $this->dayStartDate = $dayStartDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('ඒකාබද්ධ දින වාර්තාව - ' . $this->dayStartDate->format('Y-m-d'))
                    ->to('nethmavilha@gmail.com')
                    ->view('emails.day_start_report')
                    ->with([
                        'dayStartReportData' => $this->dayStartReportData,
                        'grnReportData' => $this->grnReportData,
                        'salesReportData' => $this->salesReportData,
                        'dayStartDate' => $this->dayStartDate,
                    ]);
    }
}