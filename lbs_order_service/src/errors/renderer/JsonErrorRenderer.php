<?php

namespace lbs\order\errors\renderer;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

class JsonErrorRenderer implements ErrorRendererInterface
{
  public function __invoke(Throwable $exception, bool $displayErrorDetails): string
  {
    $data = ['type' => 'error',
        'code' => $exception->getCode(),
        'message' => $exception->getMessage()];
    if ($displayErrorDetails) $data['details'] = [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()];
    return json_encode($data, JSON_PRETTY_PRINT);
  }
}
