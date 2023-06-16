<?php

namespace Mateodioev\HttpRouter;

use Closure;
use Mateodioev\StringVars\Matcher as StrMatcher;

class Route
{

    protected StrMatcher $vars;
    protected array $params = [];

    public function __construct(
        protected string $uri,
        protected Closure $action
    ) {
        $this->vars = new StrMatcher($this->uri);
    }

    /**
     * Return true if uri match with endpoint called
     */
    public function match(string $uri): bool
    {
        return $this->vars->isValid($uri, true);
    }

    /**
     * Set parameters from uri
     */
    public function params(string $uri = ''): array
    {
        if (empty($this->params)) {
            $this->params = $this->vars->match($uri, true);
        }

        return $this->params;
    }

    /**
     * Return true if the uri has parameters
     */
    public function hasParameters(): bool
    {
        return \count($this->vars->parameters()) > 0;
    }

    /**
     * Get uri
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get action to execute when uri match with endpoint called
     */
    public function action(): Closure
    {
        return $this->action;
    }
}
