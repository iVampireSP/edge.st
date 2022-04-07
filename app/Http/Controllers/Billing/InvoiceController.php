<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // 发票控制器

    public function index()
    {
        $invoices = Invoice::where('user_id', user()->id)->get();

        return $invoices;
    }
}
