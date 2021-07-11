<?php

namespace Modules\Opt\Repositories\Cache;

use Modules\Core\Repositories\Cache\BaseCacheDecorator;
use Modules\Otp\Repositories\OneTimePasswordRepository;

class CacheOneTimePasswordDecorator extends BaseCacheDecorator implements OneTimePasswordRepository
{
    /**
     * @var OneTimePasswordRepository
     */
    protected $repository;

    public function __construct(OneTimePasswordRepository $repository)
    {
        parent::__construct();
        $this->entityName = 'onetimepassword';
        $this->repository = $repository;
    }
}
