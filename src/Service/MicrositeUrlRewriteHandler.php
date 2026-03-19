<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Resource\Service\LangPathService;
use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Service\UrlRewriterHandler;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(id: 'atoolo_rewrite.url_rewrite_handler.microsite')]
class MicrositeUrlRewriteHandler implements UrlRewriterHandler
{
    public function __construct(
        private readonly ?MicrositeContext $micrositeContext,
        private readonly ?MountService $mountService,
        #[Autowire(service: 'atoolo_resource.lang_path_service')]
        private readonly LangPathService $langPathService,
    ) {}

    public function rewrite(
        Url $url,
        UrlRewriterHandlerContext $context,
    ): Url {

        if ($this->micrositeContext === null) {
            return $url;
        }

        if ($url->path === null) {
            return $url;
        }

        if ($url->host !== null) {
            return $url;
        }

        if ($this->isMicrositePath($url->path)) {
            return $this->cutMicrositePath($url, $this->micrositeContext->micrositePath);
        }

        if ($this->mountService === null) {
            return $url;
        }


        if (!$this->mountService->isMountable($url->path)) {
            return $this->toMainHostUrl($url);
        }

        return $this->mountService->toMountedUrl($url);
    }

    private function toMainHostUrl(Url $url): Url
    {
        return $url->toBuilder()
            ->scheme('https')
            ->host($this->micrositeContext->mainHost ?? '')
            ->build();
    }

    private function cutMicrositePath(Url $url, string $micrositePath): Url
    {
        $langPath = $this->langPathService->parse($url->path ?? '/');

        $path = ($langPath->lang !== null ? '/' . $langPath->lang : '') . substr(
            $langPath->path ?? '/',
            strlen($micrositePath),
        );
        if (empty($path)) {
            $path = '/';
        }
        return $url->toBuilder()->path($path)->build();
    }

    private function isMicrositePath(string $path): bool
    {
        $langPath = $this->langPathService->parse($path);
        return $this->micrositeContext !== null && str_starts_with(
            $langPath->path,
            $this->micrositeContext->micrositePath,
        );
    }
}
