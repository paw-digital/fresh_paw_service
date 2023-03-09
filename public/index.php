<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../inc/config.php';

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

$app = \Slim\Factory\AppFactory::create();
$app->get('/createDeposit', function (Request $request, Response $response) {
    return (new \Paw\Controller\ApiController($request, $response))->createDepositAccount();
});
$app->get('/cron', function (Request $request, Response $response) {
    return (new \Paw\Controller\CronController($request, $response))->run();
});
$app->run();