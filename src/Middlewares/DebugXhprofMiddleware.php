<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugXhprofMiddleware implements MiddlewareInterface
{

    protected string $profiles_directory = '';

    public function __construct(string $profiles_directory)
    {
        $this->profiles_directory = $profiles_directory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        defined('DIR_ROOT') or define('DIR_ROOT', realpath(dirname($_SERVER['DOCUMENT_ROOT'], 1)));

        if (function_exists('xhprof_enable') && PHP_SAPI != 'cli') {
            xhprof_enable(XHPROF_FLAGS_MEMORY);
        }

        $response = $handler->handle($request);

        if (function_exists('xhprof_disable') && PHP_SAPI != 'cli') {
            $xhprof_data = xhprof_disable();

            include_once DIR_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'profiles' . "/xhprof_lib/utils/xhprof_lib.php";
            include_once DIR_ROOT . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'profiles' . "/xhprof_lib/utils/xhprof_runs.php";

            $xhprof_runs = new \XHProfRuns_Default();
            $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");

            $url = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost() . "/profiles/xhprof_html/index.php?run={$run_id}&source=xhprof_testing";
            $response = $response->withHeader('X_XHProf', $url);
        }

        return $response;
    }
}