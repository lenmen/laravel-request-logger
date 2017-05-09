<?php
namespace Prettus\RequestLogger\Middlewares;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Prettus\RequestLogger\Jobs\LogTask;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Closure;

class ResponseLoggerMiddlewareLaravel50
{
    use DispatchesCommands;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        if(!$this->excluded($request)) {                    
            $task = new LogTask($request, $response);

            if($queueName = config('request-logger.queue')) {
                $this->dispatch(is_string($queueName) ? $task->onQueue($queueName) : $task);
            } else {
                $task->handle();
            }
        }
    }

    protected function excluded(Request $request) {
        $exclude = config('request-logger.exclude');
		
		if (null === $exclude || empty($exclude)) {
			return false;
		}

        foreach($exclude as $path) {
            if($request->is($path)) return true;
        }

        return false;
    }
}
