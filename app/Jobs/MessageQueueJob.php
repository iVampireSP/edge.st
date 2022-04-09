<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class MessageQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $channel, $message, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($channel, $message, $type)
    {
        $this->channel = $channel;
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $data = Message::create([
            'channel' => $this->channel,
            'type' => $this->type,
            'message' => $this->message
        ]);

        Redis::publish($this->channel, json_encode($data));

        return true;
    }
}
