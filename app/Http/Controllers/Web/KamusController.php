<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KamusKBBI;
use Yajra\DataTables\DataTables;

class KamusController extends Controller
{
    public function list()
    {
        $results = KamusKBBI::select(['id', 'word']);
        return DataTables::of($results)->make(true);
    }
}