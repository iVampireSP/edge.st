<?php

use App\Models\User;
use Laravel\SerializableClosure\SerializableClosure;

function success($data = null, $msg = 'success')
{
    return response([
        'code' => 1,
        'data' => $data,
        'msg' => $msg,
        'status' => true
    ]);
}

function failed($data = null, $msg = 'failed')
{
    return response([
        'code' => 0,
        'data' => $data,
        'msg' => $msg,
        'status' => false
    ]);
}


function userHas(array | object | null $model)
{
    if (is_null($model)) {
        return abort(404);
    }

    $user_id = user()->id;

    if (is_array($model)) {
        $result = $model['user_id'] == $user_id ? true : abort(403);
    }

    if (is_object($model)) {
        $result = $model->user_id == $user_id ? true : abort(403);
    }

    return $result;
}


// send user realtime message
function write($msg, $user_id = false)
{
    if (!$user_id) {
        $user_id = auth()->id();
    }

    return true;
}

function displayModel($model, object $console = null)
{
    $text = '';
    $model = $model->toArray();

    foreach ($model as $key => $value) {
        if ($console) {
            $text .= '  ';
        }

        $text .= "{$key}: $value" . PHP_EOL;
    }

    if ($console) {
        $console->info($text);
    }

    return $text;
}

function displayAndEditModel($console, $model)
{
    $text = '';
    $model_data = $model->toArray();

    $pass = config('pass-column.columns', []);


    displayModel($model, $console);

    foreach ($model_data as $key => $value) {
        $text .= "{$key}: $value" . PHP_EOL;

        if (in_array($key, $pass)) {
            continue;
        }

        $value = $console->ask($key, $value);

        $model->$key = $value;
    }

    $model->save();

    $console->info("Product {$model->id}#{$model->name} updated.");

    return $text;
}


function addBalanceToUser($amount, $user_id = false)
{
    return (new User())->addBalance($amount, $user_id);
}

function costBalanceFromUser($amount, $user_id = false)
{
    return (new User())->costBalance($amount, $user_id);
}


function user()
{
    if (is_null(auth()->user())) {
        return auth('api')->user();
    } else {
        return auth()->user();
    }
}

function reverse_slash($str)
{
    $str = str_replace('/', '\\', $str);
    // $str = str_replace("\\", '/', $str);

    return $str;
}

function reverse_backslash($str)
{
    $str = str_replace('\\', '/', $str);

    return $str;
}

function asyncJob($closure, $user_id = null, $message = null)
{
    // create a new task
    $task = App\Models\Task::create([
        'user_id' => $user_id,
        'comment' => $message ?? 'Task started at ' . \Illuminate\Support\Carbon::now()->toDateTimeString() . '.',
        'status' => 'pending',
    ]);

    dispatch(function () use ($closure, $task) {
        $closure();

        $task->update([
            'status' => 'success',
            'end_at' => \Illuminate\Support\Carbon::now()
        ]);
    })->catch(function () use ($task) {
        $task->update([
            'status' => 'failed',
            'end_at' => \Illuminate\Support\Carbon::now()
        ]);
    });



    return $task;
}
