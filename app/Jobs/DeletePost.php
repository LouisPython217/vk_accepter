<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use VK\Exceptions\Api\VKApiWallAccessPostException;
use VK\Exceptions\VKApiException;
use VK\Exceptions\VKClientException;

class DeletePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * [
     * 'owner_id',
     * 'post_id',
     * 'access_token'
     * ]
     */
    protected $payload;


    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @param \VK\Client\VKApiClient $vk
     * @return void
     * @throws \VK\Exceptions\VKApiException
     * @throws \VK\Exceptions\VKClientException
     */
    public function handle(\VK\Client\VKApiClient $vk)
    {

        try {
            $vk->wall()->delete(
                $this->payload['access_token'],
                Arr::except($this->payload, ['access_token'])
            );
        } catch (VKApiWallAccessPostException $e) {
            // in most cases post already deleted
        } catch (VKApiException $e) {
            if ($e->getCode() != 210) {
                throw $e;
            }
            // in most cases post already deleted
        }
    }
}
