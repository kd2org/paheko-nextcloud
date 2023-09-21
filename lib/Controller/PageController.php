<?php

namespace OCA\Paheko\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;

const PAHEKO_DIR = __DIR__ . '/../../paheko';
const PAHEKO_CONFIG_FILE = PAHEKO_DIR . '/config.local.php';
const PAHEKO_URL = 'https://fossil.kd2.org/paheko/uv/install.php';

class PageController extends Controller {

	public function __construct($appName, IRequest $request) {
		parent::__construct($appName, $request);
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
		if (false !== strpos($path, '..')) {
			die('FAIL');
		}

		if (!file_exists(PAHEKO_DIR)) {
			mkdir(PAHEKO_DIR, 0770, true);
			copy(PAHEKO_URL, PAHEKO_DIR . '/install.php');
		}

		if (file_exists(PAHEKO_DIR . '/install.php')) {
			require PAHEKO_DIR . '/install.php';
			exit;
		}

		$url = \OC::$server->getURLGenerator()->linkToRoute('paheko.page.index') . 'app/';

		if (!file_exists(PAHEKO_CONFIG_FILE)) {
			file_put_contents(PAHEKO_CONFIG_FILE, "<?php\n"
				. "namespace Paheko;\n\n"
				. "const SECRET_KEY = " . var_export(sha1(random_bytes(16))) . ";\n"
				. "if (!defined('\\Paheko\\LOCAL_LOGIN')) {\n"
				. "  http_response_code(403);\n"
				. "  die('Access forbidden');\n"
				. "}\n"
			);
		}

		$first = strtok(trim($path, '/'), '/');
		$path = rtrim(PAHEKO_DIR . '/www/' . $path, '/');

		define('Paheko\WWW_URI', $url);

		define('Paheko\LOCAL_LOGIN', [
			'user' => ['_name' => 'NextCloud'],
			'permissions' => ['users' => 9, 'config' => 9, 'web' => 9, 'accounting' => 9, 'documents' => 9]
		]);

		header_remove('Content-Security-Policy');

		if ($first === 'admin') {
			if (substr($path, -4) === '.php' && file_exists($path)) {
				require $path;
				exit;
			}
			elseif (file_exists($path . '/index.php')) {
				require $path . '/index.php';
				exit;
			}
			elseif (file_exists($path)) {
				$type = substr($path, strrpos($path, '.')+1);

				if ($type === 'css') {
					$type = 'text/css';
				}
				elseif ($type === 'js') {
					$type = 'text/javascript';
				}
				elseif ($type === 'png') {
					$type = 'image/png';
				}
				elseif ($type === 'jpg') {
					$type = 'image/jpeg';
				}
				elseif ($type === 'svg') {
					$type = 'image/svg+xml';
				}
				elseif ($type === 'html') {
					$type = 'text/html';
				}

				header('Content-Type: ' . $type . ';charset="utf-8"', true);

				readfile($path);
				exit;
			}
		}

		require PAHEKO_DIR . '/www/_route.php';
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