<?php

namespace Mateodioev\HttpRouter;

class Response
{
    protected int $status = 200;

    /**
     * Response header
     * @var array<string, string[]>
     */
    protected array $headers = [];

    /**
     * Response content
     */
    protected ?string $content = null;

    public function status(): int
    {
        return $this->status;
    }

    /**
     * Set HTTP status code
     */
    public function setStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array<string, string[]>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Set HTTP header
     */
    public function setHeader(string $key, string $value): static
    {
        $this->headers[\strtolower($key)][] = $value;
        return $this;
    }

    public function removeHeader(string $key): static
    {
        unset($this->headers[\strtolower($key)]);
        return $this;
    }

    public function setContentType(string $type): static
    {
        return $this->setHeader('Content-Type', $type);
    }

    public function setContent(?string $content = null): static
    {
        $this->content = $content;
        $this->setHeader('Content-Length', \strlen($content));
        return $this;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function prepare(): void
    {
        if (\is_null($this->content())) {
            $this->removeHeader('Content-Type');
            $this->removeHeader('Content-Length');
        } else {
            $this->setHeader('Content-Length', \strlen($this->content()));
        }

        $this->setHeader('X-Powered-By', NativeServer::POWERED_BY);
    }

    public static function create(): static
    {
        return new static();
    }

    /**
     * Plain text response
     */
    public static function text(string $text): static
    {
        return self::create()
            ->setContentType('text/plain')
            ->setContent($text);
    }

    /**
     * JSON response
     */
    public static function json(array $data): static
    {
        return self::create()
            ->setContentType('application/json')
            ->setContent(\json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * HTML response
     */
    public static function html(string $body): static
    {
        return self::create()
            ->setContentType('text/html')
            ->setContent($body);
    }

    /**
     * Redirect response
     */
    public static function redirect(string $uri, int $status = 302): static
    {
        return self::create()
            ->setStatus($status)
            ->setHeader('Location', $uri);
    }
}