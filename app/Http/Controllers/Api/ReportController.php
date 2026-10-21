<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupplierReportService;

class ReportController extends Controller
{
    public function supplierSpend(SupplierReportService $service)
    {
        $report = $service->getSpendReport();

        return response()->json([
            'data' => $report,
        ]);
    }
}
