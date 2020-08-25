<?php


namespace Imdhemy\Purchases\GooglePlay\Product;

use GuzzleHttp\Client;
use Imdhemy\Purchases\Exceptions\CouldNotCreateGoogleClient;
use Imdhemy\Purchases\GooglePlay\ClientFactory;
use Imdhemy\Purchases\GooglePlay\Contracts\CheckerInterface;
use Imdhemy\Purchases\GooglePlay\Contracts\ResponseInterface;

/**
 * Class Product
 * @package Imdhemy\Purchases\GooglePlay\Product
 */
class Product implements CheckerInterface
{
    const URI_FORMAT = "androidpublisher/v3/applications/%s/purchases/products/%s/tokens/%s";

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Response
     */
    private $response;

    /**
     * Product constructor.
     * @param string $id
     * @param string $token
     * @param Client $client
     */
    public function __construct(string $id, string $token, Client $client)
    {
        $this->id = $id;
        $this->token = $token;
        $this->client = $client;
    }

    /**
     * @param string $id
     * @param string $token
     * @return static
     * @throws CouldNotCreateGoogleClient
     */
    public static function check(string $id, string $token): self
    {
        return new self($id, $token, ClientFactory::create([ClientFactory::SCOPE_ANDROID_PUBLISHER]));
    }

    /**
     * @return Response
     */
    public function getResponse(): ResponseInterface
    {
        if (is_null($this->response)) {
            $content = $this->client->get($this->getUri())->getBody()->getContents();
            $this->response = Response::fromArray(json_decode($content, true));
            $this->response->setPurchaseToken($this->token);
        }

        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        // TODO: Implement isValid() method.
    }

    /**
     * @return string
     */
    private function getUri(): string
    {
        return sprintf(self::URI_FORMAT, $this->getPackageName(), $this->id, $this->token);
    }

    /**
     * @return string
     */
    private function getPackageName(): string
    {
        return config('purchases.google_play_package');
    }
}