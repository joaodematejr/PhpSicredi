<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (
        Request $request,
        Response $response
    ) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    //IMPLEMENTAÇÃO DO JOÃO
    $app->get('/exemploJson', function (Request $request, Response $response) {
        $data = ['name' => 'Bob 01', 'age' => 40];
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/cep', function (Request $request, Response $response) {
        $cep = '88050000';
        $link = "https://viacep.com.br/ws/$cep/json/";
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $responseAPI = curl_exec($ch);
        curl_close($ch);
        $response->getBody()->write($responseAPI);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/basic', function (Request $request, Response $response) {
        $body = '<SOAP-ENV:Envelope ...>...</SOAP-ENV:Envelope>';
        $ch = curl_init('https://test.ipg-online.com/ipgapi/services');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'WS101._.007:myPW');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $payload = json_encode($ch);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'text/xml')->withStatus(200);
    });

    $app->get('/phpCurl', function (Request $request, Response $response) {
        // telling cURL to verify the server certificate:
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        // setting the path where cURL can find the certificate to verify the
        // received server certificate against:
        curl_setopt($ch, CURLOPT_CAINFO, '/Users/joaodematejr/Documents/Github/PhpSicredi/sicredi/keys/WS2724189910._.1.pem');
        curl_setopt($ch, CURLOPT_SSLCERT, "/Users/joaodematejr/Documents/Github/PhpSicredi/sicredi/keys/WS2724189910._.1.pem");
        // setting the path where cURL can find the client certificate’s
        // private key:    
        curl_setopt($ch, CURLOPT_SSLKEY, "/Users/joaodematejr/Documents/Github/PhpSicredi/sicredi/keys/WS2724189910._.1.key");
        // setting the key password:
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "ckp_1193927132");

    });
};
