<?php

namespace OCA\Paheko\Controller;

use OCP\IUser;
use OCP\IUserSession;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;

use OCP\AppFramework\Http\NotFoundResponse;

const PAHEKO_DIR = __DIR__ . '/../../paheko';
const PAHEKO_CONFIG_FILE = PAHEKO_DIR . '/config.local.php';
const PAHEKO_URL = 'https://fossil.kd2.org/paheko/uv/install.php';

class PageController extends Controller {
	protected string $user_name;

	public function __construct($appName, IRequest $request, IUserSession $userSession) {
		parent::__construct($appName, $request);
		$user = $userSession->getUser();
		$this->user_name = ($user instanceof IUser) ? $user->getDisplayName() : 'NextCloud';
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index() {
		$nonce = sha1(random_bytes(8));
		$oTemplate = new TemplateResponse('paheko', 'index', [$nonce]);

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedFrameDomain("'self' 'nonce-$nonce'");
		//$csp->addAllowedFrameAncestorDomain("'self'");
		$oTemplate->setContentSecurityPolicy($csp);
		return $oTemplate;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function app(?string $path)
	{
		try {
			return $this->route($path);
		}
		catch (\Paheko\UserException $e) {
			// NextCloud is disabling our own error manager, so we need to catch this
			\Paheko\user_error($e);
			exit;
		}
		catch (\Throwable $e) {
			echo '<pre>' . $e;
			exit;
		}
	}

	protected function route(?string $path)
	{
		// Make sure path traversal is not allowed
		if (false !== strpos($path, '..')) {
			return new NotFoundResponse;
		}

		// This is required to allow including scripts
		header_remove('Content-Security-Policy');


		// Download installer
		if (!file_exists(PAHEKO_DIR)) {
			mkdir(PAHEKO_DIR, 0770, true);
			copy(PAHEKO_URL, PAHEKO_DIR . '/install.php');

			// Make sure Paheko cannot be directly accessed
			file_put_contents(PAHEKO_DIR . '/.htaccess', <<<EOF
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
EOF);
		}

		// Run installer
		if (file_exists(PAHEKO_DIR . '/install.php')) {
			require PAHEKO_DIR . '/install.php';
			exit;
		}

		// We have to append 'app' so that this hits the 'app' method
		// (not sure how to get nextcloud to route everything to one method, 'catch-all' style)
		$url = \OC::$server->getURLGenerator()->linkToRoute('paheko.page.index') . 'app/';

		if (!$path) {
			$path = '/admin/index.php';
		}

		$first = strtok(trim($path, '/'), '/');
		$path = rtrim(PAHEKO_DIR . '/www/' . $path, '/');

		// Custom CSS rules to match better with NextCloud styling
		if ($first === 'custom.css') {
			header('Content-Type: text/css');
			readfile(__DIR__ . '/../../nextcloud.css');
			exit;
		}
		// Serve static files
		elseif (file_exists($path) && is_file($path) && !strpos($path, '.php') && substr($path, -1) !== '/') {
			return $this->serveStatic($path);
		}

		if (!file_exists(PAHEKO_CONFIG_FILE)) {
			file_put_contents(PAHEKO_CONFIG_FILE, "<?php\n"
				. "namespace Paheko;\n\n"
				. "const SECRET_KEY = " . var_export(sha1(random_bytes(16)), true) . ";\n"
				. "if (!defined('\\Paheko\\LOCAL_LOGIN')) {\n"
				. "  http_response_code(403);\n"
				. "  die('Access forbidden');\n"
				. "}\n"
			);
		}

		define('Paheko\WWW_URI', $url);

		define('Paheko\LOCAL_LOGIN', [
			'user' => ['_name' => $this->user_name, 'id' => null],
			'permissions' => ['users' => 9, 'config' => 9, 'web' => 0, 'accounting' => 9, 'documents' => 0]
		]);

		$themingDefaults = \OC::$server->getThemingDefaults();
		$primary = $themingDefaults->getColorPrimary();
		$primary = substr($primary, 1);

		if (strlen($primary) === 6) {
			$primary = str_split($primary, 2);
		}
		elseif (strlen($primary) === 3) {
			$primary = str_split($primary, 3);
			$primary = array_map(fn($a) => $a . $a, $primary);
		}
		else {
			$primary = null;
		}

		if ($primary) {
			$primary = array_map('hexdec', $primary);
			$color1 = array_map(fn($a) => max(30, $a * 0.8), $primary);
			$color2 = array_map(fn($a) => min(254, $a * 1.2), $primary);
			$color1 = sprintf('#%02X%02X%02X', $color1[0], $color1[1], $color1[2]);
			$color2 = sprintf('#%02X%02X%02X', $color2[0], $color2[1], $color2[2]);
			define('Paheko\ADMIN_COLOR1', $color1);
			define('Paheko\ADMIN_COLOR2', $color2);
		}

		define('Paheko\ADMIN_CUSTOM_CSS', $url . 'custom.css');

		if ($first === 'custom.css') {
			header('Content-Type: text/css');
			echo <<<EOF
			html, body { background: transparent; height: 100%; }
			#menu { background-color: rgba(var(--gBgColor), 0.7); }
			#menu a { color: rgb(var(--gTextColor)); font-weight: normal; }
			main { background-color: #fff; min-height: calc(100% - 2em); }
			#menu h3 span[data-icon]::before { color: rgba(var(--gTextColor), 0.5); }
			body::-webkit-scrollbar {
				width: 8px;
				margin: 2em 0;
				background: rgba(var(--gBgColor), 0.25);
			}

			body::-webkit-scrollbar-thumb {
				background: rgba(var(--gBgColor), 0.5);
			}

			/* View transitions between page loads */
			@view-transition {
			    navigation: auto;
			}
EOF;
			exit;
		}

		if ($first === 'admin') {
			if (substr($path, -4) === '.php' && file_exists($path)) {
				require $path;
				exit;
			}
			elseif (file_exists($path . '/index.php')) {
				require $path . '/index.php';
				exit;
			}
		}

		// Fallback to router
		require PAHEKO_DIR . '/www/_route.php';

		exit;
	}

	const STATIC_TYPES = [
		'css'   => 'text/css',
		'js'    => 'text/javascript',
		'html'  => 'text/html',
		'jpg'   => 'image/jpeg',
		'jpeg'  => 'image/jpeg',
		'png'   => 'image/png',
		'gif'   => 'image/gif',
		'webp'  => 'image/webp',
		'svg'   => 'image/svg+xml',
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
		'eot'   => 'application/vnd.ms-fontobject',
		'ttf'   => 'font/truetype',
		'otf'   => 'font/opentype',
	];

	protected function serveStatic(string $path)
	{
		$ext = substr($path, strrpos($path, '.')+1);

		$type = self::STATIC_TYPES[$ext] ?? null;

		if (!$type) {
			return new NotFoundResponse;
		}

		header('Content-Type: ' . $type . ';charset="utf-8"', true);

		readfile($path);
		exit;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function appGet(string $path) {
		return $this->app($path);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function appPost(?string $path) {
		return $this->app($path);
	}
}