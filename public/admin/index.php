<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use KreaKit\Core\Session;

Session::start(app_config('session', []));
require_admin();

redirect(url('/admin/dashboard.php'));
