<?php

namespace App\Http\Controllers\Api;

use App\User;
use DateTime;
use GuzzleHttp\Psr7\Response;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class TokenController extends Controller{
    use AuthenticatesUsers;

    function generateUniqIndentifier(){
        try{
            return bin2hex(random_bytes(40));
        }catch (\TypeError $e){
            throw OAuthServerException::serverError("Terjadi Kesalahaan");
        }catch (\Error $e){
            throw OAuthServerException::serverError("Terjadi Kesalahaan");
        }catch (\Exception $e){
            throw OAuthServerException::serverError("Terjadi Kesalahaan");
        }
    }


    function createPassportTokenByClient(Request $request){
        $checkCLient = Client::where([
            "id" => $request->header("clientId"),
            "secret" => $request->header("clientSecret")
        ])->first();

        if ($checkCLient==null){
            return response()->json([
                "status" => 401,
                "pesan" => "Client Tidak Di Kenali"
            ],401);
        }

        $accessToken = new AccessToken(null);
        $accessToken->setIdentifier($this->generateUniqIndentifier());
        $accessToken->setClient(new \Laravel\Passport\Bridge\Client($checkCLient->id, null, null));
        $accessToken->setExpiryDateTime((new DateTime())->add(Passport::tokensExpireIn()));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
        $accessTokenRepository->persistNewAccessToken($accessToken);

        $response = new BearerTokenResponse();
        $response->setAccessToken($accessToken);
        $privateKey = new CryptKey('file://'.Passport::keyPath('oauth-private.key'),null,false);

        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        $response = $response->generateHttpResponse(new Response);
        $bearerToken = json_decode($response->getBody()->__toString(), true);
        return $bearerToken;
    }

    function createPassportTokenPersonalByUser(Request $request){
        $credentials = [
            ((filter_var($request->header("username"), FILTER_VALIDATE_EMAIL))?'email':'name') => $request->header("username"),
            'password' => $request->header("password"),
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('')->accessToken;
            return response()->json(['token' => $token]);
        }
    }

    function createPassportTokenByUser(Request $request){
        /*$user = User::where('email', $request->header("username"))->get()->first();
        if ($user==null){
            return response()->json([
                "status" => 401,
                "pesan" => "Maaf, Akun Tidak Dikenali"
            ],422);
        }*/

        $credentials = [
            ((filter_var($request->header("username"), FILTER_VALIDATE_EMAIL))?'email':'name') => $request->header("username"),
            'password' => $request->header("password"),
        ];

        if (!$this->guard()->attempt($credentials)){
            return response()->json([
                "status" => 401,
                "pesan" => "Maaf, Akun Tidak Dikenali"
            ],422);
        }
        $user = $this->guard()->user();
        $jwtParse = new Parser();
        $jwtParse = $jwtParse->parse(str_replace("Bearer ","",$request->header("Authorization")));

        $accessToken = new AccessToken($user->id);
        $accessToken->setIdentifier($this->generateUniqIndentifier());
        $accessToken->setClient(new \Laravel\Passport\Bridge\Client($jwtParse->getClaim("aud"), null, null));
        $accessToken->setExpiryDateTime((new DateTime())->add(Passport::tokensExpireIn()));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
        $accessTokenRepository->persistNewAccessToken($accessToken);

        $response = new BearerTokenResponse();
        $response->setAccessToken($accessToken);
        $privateKey = new CryptKey('file://'.Passport::keyPath('oauth-private.key'),null,false);

        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        $response = $response->generateHttpResponse(new Response);
        $bearerToken = json_decode($response->getBody()->__toString(), true);
        return $bearerToken;
    }
}
