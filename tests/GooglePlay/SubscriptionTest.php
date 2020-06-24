<?php

namespace Imdhemy\Purchases\Tests\GooglePlay;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Imdhemy\Purchases\Exceptions\CouldNotCreateGoogleClient;
use Imdhemy\Purchases\Exceptions\CouldNotCreateSubscription;
use Imdhemy\Purchases\Exceptions\CouldNotPersist;
use Imdhemy\Purchases\GooglePlay\Subscriptions\Response;
use Imdhemy\Purchases\GooglePlay\Subscriptions\Subscription;
use Imdhemy\Purchases\Models\SubscriptionPurchase;
use Imdhemy\Purchases\Tests\TestCase;

/**
 * Class SubscriptionTest
 * @package Imdhemy\Purchases\Tests\GooglePlay
 */
class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->id = config('purchases.subscription_id');
        $this->token = config('purchases.purchase_token');
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     */
    public function it_can_get_subscription_response()
    {
        $response = Subscription::check($this->id, $this->token)->getResponse();
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     */
    public function it_can_create_subscription_purchase()
    {
        $purchase = Subscription::check($this->id, $this->token)->toPurchase();
        $this->assertInstanceOf(SubscriptionPurchase::class, $purchase);
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     */
    public function it_returns_true_if_it_was_unique()
    {
        $isUnique = Subscription::check($this->id, $this->token)->isUnique();
        $this->assertTrue($isUnique);
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     */
    public function it_returns_false_if_it_was_not_unique()
    {
        factory(SubscriptionPurchase::class)->create([
            'purchase_token' => $this->token,
        ]);

        $isUnique = Subscription::check($this->id, $this->token)->isUnique();
        $this->assertFalse($isUnique);
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     * @throws CouldNotPersist
     */
    public function it_can_persisted_if_is_unique()
    {
        $purchase = Subscription::check($this->id, $this->token)->persist();
        $this->assertInstanceOf(SubscriptionPurchase::class, $purchase);
        $this->assertDatabaseHas('subscription_purchases', [
            'purchase_token' => $this->token,
        ]);
    }

    /**
     * @test
     * @throws CouldNotCreateSubscription
     * @throws CouldNotCreateGoogleClient
     * @throws CouldNotPersist
     */
    public function it_throws_exception_if_it_was_not_unique_on_persist()
    {
        $this->expectException(CouldNotPersist::class);
        factory(SubscriptionPurchase::class)->create([
            'purchase_token' => $this->token,
        ]);
        Subscription::check($this->id, $this->token)->persist();
    }
}
