<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    #GRUPO DE ROTAS
    $app->options('/{routes:.*}', function (
        Request $request,
        Response $response
    ) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    #BARRA PARA OLA MUNDO
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    #LISTA DE USUARIOS 
    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
    
    #CANCELAMENTO NO MESMO DIA 
    $app->post('/cancelSameDay', function (Request $request, Response $response) { 
        $data = $request->getParsedBody();
        if (empty($data['user'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher User']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['pass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Pass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['certPass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CertPass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['orderId'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher OrderId']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            try {
                $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
                $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
                $xml .= '<SOAP-ENV:Header />';
                $xml .= '<SOAP-ENV:Body>';
                $xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
                $xml .= '<v1:Transaction>';
                $xml .= '<v1:CreditCardTxType>';
                $xml .= '<v1:Type>void</v1:Type>';
                $xml .= '</v1:CreditCardTxType>';
                $xml .= '<v1:TransactionDetails>';
                $xml .= '<v1:OrderId>'.$data['orderId'].'</v1:OrderId>';
                $xml .= '<v1:TDate>'.$data['tDate'].'</v1:TDate>';
                $xml .= '</v1:TransactionDetails>';
                $xml .= '</v1:Transaction>';
                $xml .= '</ipgapi:IPGApiOrderRequest>';  
                $xml .= '</SOAP-ENV:Body>';
                $xml .= '</SOAP-ENV:Envelope>';

                $ch = curl_init("https://test.ipg-online.com/ipgapi/services");
                curl_setopt($ch, CURLOPT_POST, TRUE); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                //ENVIADO NO JSON DA API
                curl_setopt($ch, CURLOPT_USERPWD, "{$data['user']}:{$data['pass']}"); 
                //curl_setopt($ch, CURLOPT_USERPWD, "WS2724189910._.1:vR[7B,6eBV");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

                //MODO PRODUÇÃO MUDAR PARA TRUE
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                curl_setopt($ch, CURLOPT_CAINFO, "C:\certs\geotrust.crt");
                curl_setopt($ch, CURLOPT_SSLCERT, "C:\certs\WS2724189910._.1.pem");
                curl_setopt($ch, CURLOPT_SSLKEY, "C:\certs\WS2724189910._.1.key");

                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $data['certPass']);
                //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "j@9EGB4]Bh");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $result = curl_exec($ch);
                $errors = curl_error($ch); 
                curl_close($ch);

                if ($result === FALSE) {
                    $payload = json_encode($errors);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
                } else {
                    $response->getBody()->write($result);
                    return $response->withHeader('Content-Type', 'application/xml')->withStatus(200);
                }
            } catch (\Throwable $th) {
                $response->getBody()->write($th);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }
    });

    #CANCELAR PAGAMENTO
    $app->post('/cancel', function (Request $request, Response $response) { 
        $data = $request->getParsedBody();
        if (empty($data['user'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher User']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['pass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Pass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['certPass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CertPass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['chargeTotal'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ChargeTotal']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['currency'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Currency']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['orderId'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher OrderId']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            try {
                $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
                $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
                $xml .= '<SOAP-ENV:Header />';
                $xml .= '<SOAP-ENV:Body>';
                $xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
                $xml .= '<v1:Transaction>';
                $xml .= '<v1:CreditCardTxType>';
                $xml .= '<v1:Type>credit</v1:Type>';
                $xml .= '</v1:CreditCardTxType>';
                $xml .= '<v1:Payment>';
                $xml .= '<v1:ChargeTotal>'.$data['chargeTotal'].'</v1:ChargeTotal>';
                $xml .= '<v1:Currency>'.$data['currency'].'</v1:Currency>';
                $xml .= '</v1:Payment>';
                $xml .= '<v1:TransactionDetails>';
                $xml .= '<v1:OrderId>'.$data['orderId'].'</v1:OrderId>';
                $xml .= '</v1:TransactionDetails>';
                $xml .= '</v1:Transaction>';
                $xml .= '</ipgapi:IPGApiOrderRequest>';
                $xml .= '</SOAP-ENV:Body>';
                $xml .= '</SOAP-ENV:Envelope>';
                $ch = curl_init("https://test.ipg-online.com/ipgapi/services");
                curl_setopt($ch, CURLOPT_POST, TRUE); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                //ENVIADO NO JSON DA API
                curl_setopt($ch, CURLOPT_USERPWD, "{$data['user']}:{$data['pass']}"); 
                //curl_setopt($ch, CURLOPT_USERPWD, "WS2724189910._.1:vR[7B,6eBV");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

                //MODO PRODUÇÃO MUDAR PARA TRUE
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                curl_setopt($ch, CURLOPT_CAINFO, "C:\certs\geotrust.crt");
                curl_setopt($ch, CURLOPT_SSLCERT, "C:\certs\WS2724189910._.1.pem");
                curl_setopt($ch, CURLOPT_SSLKEY, "C:\certs\WS2724189910._.1.key");

                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $data['certPass']);
                //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "j@9EGB4]Bh");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $result = curl_exec($ch);
                $errors = curl_error($ch); 
                curl_close($ch);

                if ($result === FALSE) {
                    $payload = json_encode($errors);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
                } else {
                    $payload = json_encode($result);
                    $response->getBody()->write($result);
                    return $response->withHeader('Content-Type', 'application/xml')->withStatus(200);
                }
            } catch (\Throwable $th) {
                $response->getBody()->write($th);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }
    });

    #PAGAMENTO VIA CREDITO
    $app->post('/credit', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        if (empty($data['user'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher User']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['pass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Pass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['certPass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CertPass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['cardNumber'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CardNumber']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['expMonth'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ExpMonth']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['expYear'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ExpYear']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['chargeTotal'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ChargeTotal']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['currency'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Currency']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            try {
                $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
                $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
                $xml .= '<SOAP-ENV:Header />';
                $xml .= '<SOAP-ENV:Body>';
                $xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
                $xml .= '<v1:Transaction>';
                $xml .= '<v1:CreditCardTxType>';
                $xml .= '<v1:Type>credit</v1:Type>';
                $xml .= '</v1:CreditCardTxType>';
                $xml.='<v1:CreditCardData>
                <v1:CardNumber>'.$data['cardNumber'].'</v1:CardNumber>
                <v1:ExpMonth>'.$data['expMonth'].'</v1:ExpMonth>
                <v1:ExpYear>'.$data['expYear'].'</v1:ExpYear>
                </v1:CreditCardData>';
                $xml .= '<v1:cardFunction>credit</v1:cardFunction>';
                $xml .= '<v1:Payment>';
                $xml .= '<v1:ChargeTotal>'.$data['chargeTotal'].'</v1:ChargeTotal>';
                $xml .= '<v1:Currency>'.$data['currency'].'</v1:Currency>';
                $xml .= '</v1:Payment>';
                $xml .= '</v1:Transaction>';
                $xml .= '</ipgapi:IPGApiOrderRequest>';
                $xml .= '</SOAP-ENV:Body>';
                $xml .= '</SOAP-ENV:Envelope>';
                $ch = curl_init("https://test.ipg-online.com/ipgapi/services");
                curl_setopt($ch, CURLOPT_POST, TRUE); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                //ENVIADO NO JSON DA API
                curl_setopt($ch, CURLOPT_USERPWD, "{$data['user']}:{$data['pass']}"); 
                //curl_setopt($ch, CURLOPT_USERPWD, "WS2724189910._.1:vR[7B,6eBV");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

                //MODO PRODUÇÃO MUDAR PARA TRUE
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                curl_setopt($ch, CURLOPT_CAINFO, "C:\certs\geotrust.crt");
                curl_setopt($ch, CURLOPT_SSLCERT, "C:\certs\WS2724189910._.1.pem");
                curl_setopt($ch, CURLOPT_SSLKEY, "C:\certs\WS2724189910._.1.key");

                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $data['certPass']);
                //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "j@9EGB4]Bh");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $result = curl_exec($ch);
                $errors = curl_error($ch); 
                curl_close($ch);

                if ($result === FALSE) {
                    $payload = json_encode($errors);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
                } else {
                    $payload = json_encode($result);
                    $response->getBody()->write($result);
                    return $response->withHeader('Content-Type', 'application/xml')->withStatus(200);
                }
            } catch (\Throwable $th) {
                $response->getBody()->write($th);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }
    });

    #PAGAMENTO VIA DEBITO
    $app->post('/debit', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        if (empty($data['user'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher User']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['pass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Pass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['certPass'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CertPass']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['cardNumber'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher CardNumber']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['expMonth'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ExpMonth']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['expYear'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ExpYear']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['chargeTotal'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher ChargeTotal']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } elseif (empty($data['currency'])) {
            $response->getBody()->write(json_encode(['message' => 'Você precisa preencher Currency']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } else {
            try {
                $xml  = '<?xml version="1.0" encoding="UTF-8"?>';
                $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
                $xml .= '<SOAP-ENV:Header />';
                $xml .= '<SOAP-ENV:Body>';
                $xml .= '<ipgapi:IPGApiOrderRequest xmlns:v1="http://ipg-online.com/ipgapi/schemas/v1" xmlns:ipgapi="http://ipg-online.com/ipgapi/schemas/ipgapi">';
                $xml .= '<v1:Transaction>';
                $xml .= '<v1:CreditCardTxType>';
                $xml .= '<v1:Type>sale</v1:Type>';
                $xml .= '</v1:CreditCardTxType>';
                $xml.='<v1:CreditCardData>
                <v1:CardNumber>'.$data['cardNumber'].'</v1:CardNumber>
                <v1:ExpMonth>'.$data['expMonth'].'</v1:ExpMonth>
                <v1:ExpYear>'.$data['expYear'].'</v1:ExpYear>
                </v1:CreditCardData>';
                $xml .= '<v1:cardFunction>debit</v1:cardFunction>';
                $xml .= '<v1:Payment>';
                $xml .= '<v1:ChargeTotal>'.$data['chargeTotal'].'</v1:ChargeTotal>';
                $xml .= '<v1:Currency>'.$data['currency'].'</v1:Currency>';
                $xml .= '</v1:Payment>';
                $xml .= '</v1:Transaction>';
                $xml .= '</ipgapi:IPGApiOrderRequest>';
                $xml .= '</SOAP-ENV:Body>';
                $xml .= '</SOAP-ENV:Envelope>';
                $ch = curl_init("https://test.ipg-online.com/ipgapi/services");
                curl_setopt($ch, CURLOPT_POST, TRUE); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                //ENVIADO NO JSON DA API
                curl_setopt($ch, CURLOPT_USERPWD, "{$data['user']}:{$data['pass']}"); 
                //curl_setopt($ch, CURLOPT_USERPWD, "WS2724189910._.1:vR[7B,6eBV");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

                //MODO PRODUÇÃO MUDAR PARA TRUE
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

                curl_setopt($ch, CURLOPT_CAINFO, "C:\certs\geotrust.crt");
                curl_setopt($ch, CURLOPT_SSLCERT, "C:\certs\WS2724189910._.1.pem");
                curl_setopt($ch, CURLOPT_SSLKEY, "C:\certs\WS2724189910._.1.key");

                curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $data['certPass']);
                //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "j@9EGB4]Bh");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $result = curl_exec($ch);
                $errors = curl_error($ch); 
                curl_close($ch);

                if ($result === FALSE) {
                    $payload = json_encode($errors);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
                } else {
                    $payload = json_encode($result);
                    $response->getBody()->write($result);
                    return $response->withHeader('Content-Type', 'application/xml')->withStatus(200);
                }
            } catch (\Throwable $th) {
                $response->getBody()->write($th);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }
    });

    #PAGAMENTO VIA PIX
    $app->post('/pix', function (Request $request, Response $response) { 
        $response->getBody()->write(json_encode(['message' => 'Pagamento via PIX']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    #PAGAMENTO VIA BOLETO
    $app->post('/boleto', function (Request $request, Response $response) { 
        $data = $request->getParsedBody();
        if (!empty($data)) {
            if (empty($data['user'])) { 
                $response->getBody()->write(json_encode(['message' => 'Faltando parametros para boleto']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }else{
                $response->getBody()->write(json_encode(['message' => 'Pagamento via Boleto']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        } else {
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    });




};
