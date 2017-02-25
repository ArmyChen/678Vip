<?php
define ( "HY_URL", "http://a2.easemob.com/1174161029115536/wsh" );
class HyTool {
	/**
	 * 获取环信token
	 *
	 * @param unknown $isReset        	
	 * @return string|mixed
	 */
	public static function getToken($isReset) {
		$fn = dirname ( __FILE__ ) . "/hy-token";
		if ($isReset) {
			
			// 删除token重新获取
			$files = glob ( $fn . "/*" );
			foreach ( $files as $file ) {
				if (is_file ( $file )) {
					unlink ( $file );
				}
			}
		}
		if (! file_exists ( $fn ) || count ( scandir ( $fn ) ) < 3) {
			if (! file_exists ( $fn )) {
				
				// 新建文件夹
				mkdir ( $fn, 0777, true );
			}
			
			// 发送环信请求
			$json = self::hyApi ( "/token", array (
					'grant_type' => 'client_credentials',
					'client_secret' => 'YXA61JeZY9FuZ7HfXseot5JYX49jTao',
					'client_id' => 'YXA64a0GUJ17EeaIc7mMvq6GIg' 
			) );
			if ($json) {
				$token = $json ["access_token"];
				
				// 创建文件夹
				fclose ( fopen ( $fn . "/" . $token, "w" ) );
				return $token;
			} else {
				return "没有token";
			}
		} else {
			
			$listFile = scandir ( $fn );
			
			// 返回改文件夹下得第一个文件
			return $listFile[2];
		}
	}
	
	/**
	 * @作者 周龙权
	 * @创建时间 2016年12月20日 上午11:50:19
	 * @描述 用户注册
	 *
	 * @param
	 *        	name
	 * @param
	 *        	pwd
	 */
	public static function regist($name, $pwd) {
		$p = array (
				"username" => $name,
				"password" => $pwd 
		);
		$json = self::hyApi ( "/users", $p );
		return $json ["error_description"];
	}
	
	/**
	 * 环信API请求
	 *
	 * @param unknown $ext        	
	 * @param array $param        	
	 * @return mixed
	 */
	private static function hyApi($ext, $param = array(),$timer = 1) {
		$header = array (
				'Content-type: application/json; charset=utf-8',
				'Accept; application/json' 
		);
		
		if ($ext != "/token") {
			$header [] = "Authorization: Bearer " . self::getToken ();
		}
		
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, HY_URL . $ext );
		
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		
		if (count ( $param ) > 0) {
			
			$dataRaw = json_encode ( $param );
			
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $dataRaw );
		}
		
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
		
		curl_setopt ( $ch, CURLOPT_HEADER, false );
		
		$rs = curl_exec ( $ch );
		
		curl_close ( $ch );
		
		// 转化为array
		$json = json_decode ( $rs, true );
		
		if (! $json) {
			return null;
		}
		
		$error = $json ["error_description"];
		if ($error == null) {
			return $json;
		} else {
			switch ($error) {
				
				/**
				 * 发送请求时使用的 token 错误，注意：不是 token 过期
				 */
				case "Unable to authenticate due to corrupt access token" :
				
				/**
				 * 无效 token，符合 token 的格式，但是该 token 不是接受请求的系统生成的，系统无法识别该 token
				 */
				case "Unable to authenticate" :
				
				/**
				 * APP的用户注册模式为授权注册，但是注册用户时请求头没带token
				 */
				case "registration is not open, please contact the app admin" :
					
					if($timer == 2){
						return $json;
					}
					
					// 强制重新获取token
					self::getToken ( true );
					return self::hyApi ( $ext, $param,$timer+1 );
					break;
				default :
					return $json;
					break;
			}
		}
	}
}
?>