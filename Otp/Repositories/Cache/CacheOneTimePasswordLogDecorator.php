<?php

namespace Modules\Opt\Repositories\Cache;

use Modules\Core\Repositories\Cache\BaseCacheDecorator;
use Modules\Otp\Repositories\OneTimePasswordLogRepository;

class CacheOneTimePasswordLogDecorator extends BaseCacheDecorator implements OneTimePasswordLogRepository
{
    /**
     * @var OneTimePasswordLogRepository
     */
    protected $repository;

    public function __construct(OneTimePasswordLogRepository $repository)
    {
        parent::__construct();
        $this->entityName = 'onetimepasswordlog';
        $this->repository = $repository;
    }
}
