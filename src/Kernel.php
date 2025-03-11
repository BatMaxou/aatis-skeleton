<?php

namespace App;

use Aatis\HttpFoundation\Component\Request;
use Aatis\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function handle(Request $request): void
    {
        parent::handle($request);
    }
}
