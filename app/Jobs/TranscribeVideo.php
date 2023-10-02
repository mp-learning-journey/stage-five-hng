<?php

namespace App\Jobs;

use App\Helpers\FileHelper;
use App\Models\Recording;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscribeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recording;
    /**
     * Create a new job instance.
     */
    public function __construct(Recording $recording)
    {
        $this->recording = $recording;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        FileHelper::transcribeVideo($this->recording);
    }
}
