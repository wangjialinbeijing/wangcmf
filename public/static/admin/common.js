function showAlertInfo(msg)
{
    if(msg)
    {
        $('#showErrorTips').find('p').text(msg);
    }
}
$(function(){
    //ajax get请求
    $('.ajax-get').click(function(){
        var target;
        var that = this;
        if ( $(this).hasClass('confirm') ) {
            if(!confirm('确认要执行该操作吗?')){
                return false;
            }
        }
        if ( (target = $(this).attr('url')) ) {
            $.post(target , {} , function(data){
                if (data.code==1) {
                    if (data.url) {
                        $('#showSuccessTips').show().find('p').text(data.msg + ',正在执行跳转...');
                    }else{
                        $('#showSuccessTips').show().find('p').text(data.msg );
                    }
                    setTimeout(function(){
                        if (data.url) {
                            window.location.href=data.url;
                        }else{
                            window.location.reload();
                        }
                    },1500);
                }else{
                    $('#showErrorTips').show().find('p').text(data.msg);
                    setTimeout(function(){
                        if (data.url) {
                            window.location.href=data.url;
                        }
                    },1500);
                }
            })

        }
        return false;
    });
});
