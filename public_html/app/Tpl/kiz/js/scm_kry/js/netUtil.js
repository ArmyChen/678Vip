/**
 * 网络工具
 */
function NetUtil(){};
	
	/**
	 * 验证ip地址
	 * @param ipString ip地址 X.X.X.X
	 */
	NetUtil.isIP = function(ipString){
		 var re =  /^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/ ;  
		 return re.test(ipString);  
	};
