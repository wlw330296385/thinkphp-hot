
if (jQuery) { 
// jQuery 已加载 
} else { 
    document.write('<script src="/static/frontend/js/jquery-3.2.1.min.js"></script>');   
} 


var wooApi = {
        //同步访问接口
        'asyncF':function (url,data) {

            //访问接口
            var postMsg = $.ajax({
                url: url,
                data: data,
                async: false,
                type: 'post',
                dataType:'json',
                success: function(msg) {

                }
            });
            return postMsg.responseJSON;
        },
        //异步访问接口
        'asyncT':function (url,data) {

            //访问接口
            var postMsg = $.ajax({
                url: url,
                data: data,
                async: false,
                type: 'post',
                dataType:'json',
                success: function(msg) {

                }
            });
            return postMsg.responseJSON;
        },
        // 一键获取已有变量
        'getJson':function(str){
            var jsonData = JSON.parse(str);
            return jsonData; 
        },


        // 一键获取input下的值
        'getInputData':function(domName = 'input',className = 'data',data = []){
       
            switch(domName){
                case 'input':
                $(domName+"."+className).each(function(i){
                    var name = $(this).attr('name');
                    var val = $(this).val();
                    var son = name+'='+val;
                    data.push(son);
                })
                break;
                case 'select':
                $(domName+"."+className).each(function(i){
                    var name = $(this).attr('name');
                    var val = $(this).val();
                    var son = name+'='+val;
                    data.push(son);
                })
                break;
                case 'checkbox':
                $(domName+"."+className).each(function(i){
                    if($(this).attr('checked')==true){
                        var name = $(this).attr('name');
                        var val = $(this).data();
                        var son = name+'='+val;
                        data.push(son);
                    }
                })
                break;
                case 'radio':
                $(domName+"."+className).each(function(i){
                    if($(this).attr('checked')==true){
                        var name = $(this).attr('name');
                        var val = $(this).val();
                        var son = name+'='+val;
                        data.push(son);
                    }
                })
                break;
            }
            return data;
        },
        'getDomData':function(className = 'data',data = []){
            $('.'+className).each(function(i){
                    var name = $(this).attr('name');
                    var val = $(this).html();
                    var son = name+'='+val;
                    data.push(son);
            })
            return data;
        },

        //发送请求
        'ajaxSend':function(url,data){
            $.ajax({
                url:url,
                data:data,
                type:'post',
                dataType:'JSON',
                error:function(result){

                },
                success:function(result){

                },

            })
        },


        //数组转对象
        'arrTOobj':function(arr){
            var obj = new Object();
            for (var x in arr){
                var split = arr[x].split('=');
                obj[split[0] ] = split[1];
            }
            return obj;
        }
    }