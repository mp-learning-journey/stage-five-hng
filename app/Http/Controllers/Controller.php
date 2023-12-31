<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(title="HNG Chrome extension - Screen Recording API", version="1.0"),
 * @OA\Server(
 *    description="Base URL",
 *    url="https://hngs5.mrprotocoll.me"
 *  )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
