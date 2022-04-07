<?php

namespace App\Jobs;

use Closure;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class AsyncJob extends Job implements ShouldQueue
{


    protected $user_id = null;
    protected $message = null;
    protected $closure;


    private Task $task;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($closure, $user_id = null, $message = null)
    {
        $this->closure = $closure;

        $this->user_id = $user_id;

        $this->message = $message;

        // create a new task
        $this->task = Task::create([
            'user_id' => $this->user_id,
            'comment' => $this->message ?? 'Task started at ' . Carbon::now()->toDateTimeString(),
            'status' => 'pending',
        ]);

        return $this->task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->task->refresh();

        $this->task->update(['status' => 'running']);

        // cache all type exceptions
        try {
            // run the closure
            ($this->closure)();

            $this->task->update(['status' => 'success']);
        } catch (\Exception $e) {
            // log the exception
            Log::error($e);

            $this->task->update(['status' => 'failed']);
        }
    }
}
