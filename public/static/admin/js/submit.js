$(function(){
    $('.ajax-post').click(function(){
        // 获取dom对象
        var _self = $(this);
        var _form_class =_self.attr('target-form');
        if(!_form_class)
        {
            return false;
        }
        var _form = $('.' + _form_class);
        var _action = _form.get(0).action;
        if(!_action)
        {
            return false;
        }
        // 防止重复提交
        _self.text('正在提交').attr('disabled' , 'disabled');
        $('#showErrorTips').hide();
        // ajax提交
        $.post(_action , _form.serialize() , function(data){
            if(data.code == 0)
            {
                $('#showErrorTips').show().find('p').text(data.msg);
                _self.text('保存').removeAttr('disabled');
                return false;
            }
            else
            {
                if (data.url) {
                    $('#showSuccessTips').show().find('p').text(data.msg + ',正在执行跳转...');
                }else{
                    $('#showSuccessTips').show().find('p').text(data.msg );
                }
                setTimeout(function(){
                    if(data.url)
                    {
                        window.location.href = data.url;
                    }
                    else
                    {
                        window.history.back();
                    }
                },1500);
            }
        });
        return false;
    })
})