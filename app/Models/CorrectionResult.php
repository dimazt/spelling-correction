<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionResult extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'correction_results';
    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;
}
