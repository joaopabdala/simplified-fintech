<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransferResource;
use App\Models\Transfer;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transfers = Transfer::orderByDesc('created_at')->paginate(5);

        return TransferResource::collection($transfers);
    }


    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        return TransferResource::make($transfer);
    }
}
