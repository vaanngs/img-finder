<?php

declare(strict_types=1);

namespace ImgFinder\Cache;

use DateInterval;
use ImgFinder\RequestInterface;
use ImgFinder\Translator\TranslatorInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheTranslator extends AbstractCache implements TranslatorInterface
{
    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var TranslatorInterface */
    private $translator;


    public function __construct(CacheItemPoolInterface $cache, TranslatorInterface $translator)
    {
        $this->cache      = $cache;
        $this->translator = $translator;
    }


    public function getName(): string
    {
        return $this->translator->getName();
    }


    /**
     * @param RequestInterface $request
     * @throws
     * @return RequestInterface
     */
    public function findWord(RequestInterface $request): RequestInterface
    {
        $key  = $this->getCacheKey($request);
        $item = $this->cache->getItem($key);

        if ($item->isHit()) {
            return unserialize($item->get());
        }

        $newRequest = $this->translator->findWord($request);
        if ($newRequest->isEqual($request)) {
            return $request;
        }

        $item->set(serialize($newRequest));
        $item->expiresAfter(new DateInterval(self::ONE_DAY));
        $this->cache->save($item);

        return $newRequest;
    }
}
