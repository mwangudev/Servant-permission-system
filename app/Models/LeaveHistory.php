<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\LeaveRequest;

class LeaveHistory extends Model
{
    protected $fillable = [
        'leave_request_id',
        'user_id',
        'action',
        'remarks',
        'created_at',
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
