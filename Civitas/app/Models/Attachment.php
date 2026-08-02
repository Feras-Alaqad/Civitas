<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasUuids;

    protected $table = 'Attachments';
    protected $primaryKey = 'AttachmentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'RequestID',
        'FilePath',
        'DocumentType',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'RequestID', 'RequestID');
    }
}
