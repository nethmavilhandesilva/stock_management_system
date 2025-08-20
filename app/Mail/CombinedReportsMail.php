<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CombinedReportsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dayStartReportData;
    public $grnReportData;
    public $salesReportData;
    public $dayStartDate;
    public $weightBasedReportData;
    public $salesByBill;
    public $salesadjustments;
    public $loans; // ✅ add this
    
    // ✅ Financial report properties
    public $financialReportData;
    public $financialTotalDr;
    public $financialTotalCr;
    public $financialProfit;
    public $financialDamages;
    public   $profitTotal ;
    public  $totalDamages;


    /**
     * Create a new message instance.
     *
     * @param mixed $dayStartReportData
     * @param mixed $grnReportData
     * @param mixed $salesReportData
     * @param \Carbon\Carbon $dayStartDate
     * @param mixed $weightBasedReportData
     * @param mixed $salesByBill
     * @param mixed $salesadjustments
     * @param mixed $financialReportData
     * @param float $financialTotalDr
     * @param float $financialTotalCr
     * @param float $financialProfit
     * @param float $financialDamages
     * @param float $profitTotal
     * @param float $totalDamages
     * @param mixed  $loans 
     *
     * @return void
     */
    public function __construct(
        $dayStartReportData,
        $grnReportData,
        $salesReportData,
        $dayStartDate,
        $weightBasedReportData,
        $salesByBill,
        $salesadjustments = null,
        $financialReportData = null,
        $financialTotalDr = 0,
        $financialTotalCr = 0,
        $financialProfit = 0,
        $financialDamages = 0,
        $profitTotal=0,
        $totalDamages=0,
         $loans ,
    ) {
        $this->dayStartReportData = $dayStartReportData;
        $this->grnReportData = $grnReportData;
        $this->salesReportData = $salesReportData;
        $this->dayStartDate = $dayStartDate;
        $this->weightBasedReportData = $weightBasedReportData;
        $this->salesByBill = $salesByBill;
        $this->salesadjustments = $salesadjustments;

        $this->financialReportData = $financialReportData;
        $this->financialTotalDr = $financialTotalDr;
        $this->financialTotalCr = $financialTotalCr;
        $this->financialProfit = $financialProfit;
        $this->financialDamages = $financialDamages;
        $this->profitTotal = $profitTotal;
        $this->totalDamages = $totalDamages;
        $this->loans = $loans;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('ඒකාබද්ධ දින වාර්තාව - ' . $this->dayStartDate->format('Y-m-d'))
                    ->to('cdesilva2005@gmail.com')
                    ->view('emails.day_start_report')
                    ->with([
                        'dayStartReportData' => $this->dayStartReportData,
                        'grnReportData' => $this->grnReportData,
                        'salesReportData' => $this->salesReportData,
                        'dayStartDate' => $this->dayStartDate,
                        'weightBasedReportData' => $this->weightBasedReportData,
                        'salesByBill' => $this->salesByBill,
                        'salesadjustments' => $this->salesadjustments,
                        'financialReportData' => $this->financialReportData,
                        'financialTotalDr' => $this->financialTotalDr,
                        'financialTotalCr' => $this->financialTotalCr,
                        'financialProfit' => $this->financialProfit,
                        'financialDamages' => $this->financialDamages,
                        'profitTotal' => $this->profitTotal,
                        'totalDamages' => $this->totalDamages,
                        'loans' => $this->loans,
                    ]);
    }
}
