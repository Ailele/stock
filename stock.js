/**
 * Created by mao on 2016/4/20.
 */
$(function(){
   $(".stock").hover(
        function (){
            var stockCode = $(this).attr('id');
            $('#img').attr('src', 'http://image.sinajs.cn/newchart/daily/n/'+stockCode+'.gif');
        },
        function (){
            
        }
   );
});