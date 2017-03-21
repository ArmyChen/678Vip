/**
 * 分页查询使用对象<br>
 * 构建分页对象，需要初始化的有pageQueryDataId、pageQueryToolId、objectName；<br>
 * 查询后需要初始化的有totalRows
 */
function PageQuery(objectName){
	/** 当前页，默认为第一页 */
	this.currentPage = 1;
	/** 上一次查询的页码 其值计算方式为(pageQuery.lastPage == null ? 1 : pageQuery.currentPage);*/
	this.lastPage = 1;
	/** 每页显示数据条数，默认20条 */
	this.pageSize = 20;
	/** 所有记录数 查询后必须初始化*/
	this.totalRows;
	/** 总页数 */
	this.totalPage;
	/** 展现页码个数，默认10个 */
	this.showtotalPageNum = 10;
	/** 是否显示总条数，默认为false */
	this.showTotalPage = false;
	/** 是否显示总记录数，默认为false */
	this.showTotalRows = false;
	/** 加载几页清空一次列表，默认5次 */
	this.clearTimes = 5;
	/** 分页数据栏div */
	this.pageQueryDataId;
	/** 分页工具栏div */
	this.pageQueryToolId;
	/** 当前一共有几页，默认为1页 */
	this.currentTotalPages = 1;
	/** 起始页 */
	this.startPage = 1;
	/** 截止页 */
	this.endPage;
	/** 创建的对象的名字 */
	this.objectName = objectName;
	/** 发生数量变更的数据 */
	this.subRows = 0;
	
	/**
	 * 计算当前总页数
	 */
	this.calculatePages = function(){
		this.totalPage = Math.ceil(this.totalRows / this.pageSize);
	};
	/**
	 * 计算subrows页数
	 */
	this.getSubRows = function(){
		if(this.lastPage != null){
			this.subRows = this.lastPage >= this.currentPage ? 0 : this.subRows;
		}
		return this.subRows;
	};
	
	/**
	 * 计算当前要显示的页码范围
	 */
	this.calculatePageScope = function(){
		var halfPages = Math.floor(this.showtotalPageNum/2);
		if(this.currentPage <= halfPages){
			this.startPage = 1;
		}else{
			this.startPage = this.currentPage - halfPages;
//			alert("startPage==" + this.startPage);
//			alert("currentPage==" + this.currentPage);
//			alert("halfPages==" + halfPages);
		}
		this.endPage = this.startPage + this.showtotalPageNum;
		this.endPage = this.endPage > this.totalPage ? this.totalPage : this.endPage - 1;
	};
	
	/**
	 * 点击上一页
	 * @param query 查询数据的方法,该方法要返回总条数
	 */
	this.prePage = function(query){
		if(this.currentPage <= 1) return; //如果小于第一页，则不再查询
		this.currentPage = this.currentPage - 1;
		this.queryPage(this.currentPage, query);
	};
	/**
	 * 点击下一页
	 * @param query 查询数据的方法,该方法要返回总条数
	 */
	this.nextPage = function(query){
		if(this.currentPage >= this.totalPage) return; //如果大于最后一页，则不再查询
		this.currentPage = this.currentPage + 1;
		this.queryPage(this.currentPage, query);
		
	};
	
	/**
	 * 点击某一页
	 * @param currentPage 要查询的页码
	 * @param query 查询数据的方法,该方法要返回总条数
	 */
	this.queryPage = function(currentPage,query){
		//showLoading();
		this.currentPage = currentPage;
		//清理当前列表中的信息
		$("#" + this.pageQueryDataId).empty();
		$("#" + this.pageQueryToolId).empty();
		query(); 
		
	};
	
	
	this.afterQuery = function(){
		if(this.currentPage == 1){
			this.subRows = 0;
		}
		//计算当前页码
//		alert("this.currentPage before ==" + this.currentPage);
		this.currentPage = this.currentPage - Math.floor(this.subRows / this.pageSize);
//		alert("Math.floor(this.subRows / this.pageSize)==" + Math.floor(this.subRows / this.pageSize) );
//		alert("this.currentPage after ==" + this.currentPage);
		this.calculatePages(); //计算当前总页数
		if(this.totalRows <= 0) {
			$("#" + this.pageQueryToolId).html("<p class='gray-1 text-center'>没有查询到符合条件的记录</p>");
			return;
		}
		this.calculatePageScope(); //计算要显示的页码范围
		this.currentTotalPages = 1; //重新初始化当前页面的数据
		this.initPageToolDiv();//初始化分页条
		//hideLoading();
		//滚动条 回到顶部
		//$(document).scrollTop(0);
	};
	
	/**
	 * 滚动下一页
	 * @param query 查询数据的方法,该方法要返回总条数
	 */
	this.scrollNextPage = function(query){
		if(this.currentTotalPages >= this.clearTimes || this.currentPage >= this.totalPage) return;
		this.currentPage = this.currentPage + 1; //当前页码加1
		
		query();
		
		if(this.currentPage >= this.endPage){
			this.startPage += 1;
			this.endPage += 1;
			this.endPage = this.endPage > this.totalPage ? this.totalPage : this.endPage;
			$("#" + this.pageQueryToolId).empty();
			this.calculatePageScope(); //计算要显示的页码范围
			this.initPageToolDiv();
		
		}else{
			var pageToolDiv = $("#" + this.pageQueryToolId + " > .current");
			pageToolDiv.removeClass("current");
			pageToolDiv.next().addClass("current");
		}
		this.calculatePages(); //计算当前总页数
		this.currentTotalPages += 1;
		
		
	};
	/**
	 * 初始化分页条
	 */
	/*
	this.initPageToolDiv2 = function(){
		if(this.currentPage > 1){
			$("#" + this.pageQueryToolId).append('<span  onclick="javascript:' + this.objectName +  '.queryPage(1,loadData)">首页</span>');
			$("#" + this.pageQueryToolId).append('<span  onclick="javascript:' + this.objectName +  '.prePage(loadData)" class="prev">&lt;</span>');
		}
		for(var i = this.startPage; i <= this.endPage; i ++ ){
			var str;
			if(i == this.currentPage){
				str = '<a href="javascript:void(0)"  onclick="javascript:' + this.objectName +  '.queryPage(' + i + ', loadData)" class="current';
				if(i==1){
					str += ' first';
				}
				str += '">' + i + '</a>';
			}else{
				str = '<a href="javascript:void(0)"  onclick="javascript:' + this.objectName +  '.queryPage(' + i + ', loadData)"';
				if(i==1){
					str += ' first';
				}
				str += '">' + i + '</a>';
			}
			$("#" + this.pageQueryToolId).append(str);
        }
		if(this.endPage < this.totalPage){
			$("#" + this.pageQueryToolId).append("<span>....</span>");
		}
		if(this.currentPage < this.totalPage){
			$("#" + this.pageQueryToolId).append('<span  onclick="javascript:' + this.objectName +  '.nextPage(loadData)" class="next">&gt;</span>');
			$("#" + this.pageQueryToolId).append('<span  onclick="javascript:' + this.objectName +  '.queryPage(' + this.totalPage + ',loadData)" >尾页</span>');
		}
		$("#" + this.pageQueryToolId).append('<span  class="total">共'+this.totalPage+'页</span>');
	};
	*/
	this.initPageToolDiv = function(){
		var bar=[],len=0;
		if(this.currentPage > 1){
			bar[len++]='<span  onclick="javascript:' + this.objectName +  '.queryPage(1,loadData)" class="home">首页</span>';
			bar[len++]='<span  onclick="javascript:' + this.objectName +  '.prePage(loadData)" class="prev">&lt; 上一页</span>';
		}
		for(var i = this.startPage; i <= this.endPage; i ++ ){
			var str = '';
			//console.log("i ==" + i);
			if(i == this.currentPage){
				str = '<a href="javascript:void(0)"  onclick="javascript:' + this.objectName +  '.queryPage(' + i + ', loadData)" class="current';
				if(i==this.startPage){
					str += ' first';
				}
				str += '">' + i + '</a>';
				bar[len++] = str;
				//console.log(bar[len - 1]);
			}else{
				str = '<a href="javascript:void(0)"  onclick="javascript:' + this.objectName +  '.queryPage(' + i + ', loadData)" ';
				if(i==this.startPage){
					str += ' class="first';
				}
				str += '">' + i + '</a>';
				bar[len++] = str;
				//console.log(bar[len - 1]);
			}
        }
		if(this.endPage < this.totalPage){
			bar[len++]="<span>....</span>";
		}
		if(this.currentPage < this.totalPage){
			bar[len++]='<span  onclick="javascript:' + this.objectName +  '.nextPage(loadData)" class="next">下一页 &gt;</span>';
			bar[len++]='<span  onclick="javascript:' + this.objectName +  '.queryPage(' + this.totalPage + ',loadData)" class="endPage">尾页</span>';
		}
		bar[len++]='<span  class="total">共 '+this.totalPage+' 页</span>';
		$("#" + this.pageQueryToolId).html(bar.join(""));
	}
	 
	//获取分页参数
	this.getPageParameter = function(prefix) {
		if(prefix != null){
			return "page.currentPage="+this.currentPage+"&page.pageSize="+this.pageSize+"&page.subRows="+this.getSubRows();
		}else{
			return "currentPage="+this.currentPage+"&pageSize="+this.pageSize+"&subRows="+this.getSubRows();
		}
	};
};
