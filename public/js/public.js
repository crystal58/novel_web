$(document).ready(function(){
    //鼠标悬停变换图片 <img data-replace-img="替换图片地址" src="" />
    $('img').mouseover(function(){
        var datatext=$(this).data("replace-img");
        if (datatext!=''){
            var srctext=$(this).attr('src');
            $(this).data("replaceold-img", srctext);
            $(this).attr('src',datatext)
        }
    });
    $('img').mouseout(function(){
        var datatext=$(this).data("replaceold-img");
        if (datatext!=''){
            var srctext=$(this).attr('src');
            $(this).data("replace-img", srctext);
            $(this).attr('src',datatext)
        }
    });
    //分页
    var prevnum=2;
    var nextnum=2;
    var dqnum=parseInt($('.am-pagination .am-active a').text());

    $('.am-pagination-next').before('<li id="wos-pagination-next-more" style="display: none"><a class="am-hide-sm">...</a></li>')
    $('.am-pagination-prev').after('<li id="wos-pagination-prev-more"  style="display: none"><a class="am-hide-sm">...</a></li>')
    $(".am-pagination li a").each(function(){
        var dq=$(this).text()
        if (dq>dqnum+nextnum){
            $(this).parent("li").hide();
            $('#wos-pagination-next-more').show();
        }
        if (dq<dqnum-prevnum){
            $(this).parent("li").hide();
            $('#wos-pagination-prev-more').show();
        }
    })
    $('#wos-pagination-next-more').click(function(){
        $(".am-pagination li a").each(function(){
            var dq=$(this).text()
            if (dq>dqnum+nextnum){
                $(this).parent("li").show();
            }
        });
        $('#wos-pagination-next-more').hide();
    });
    $('#wos-pagination-prev-more').click(function(){
        $(".am-pagination li a").each(function(){
            var dq=$(this).text()
            if (dq<dqnum-prevnum){
                $(this).parent("li").show();
            }
        });
        $('#wos-pagination-prev-more').hide();
    });

  var countnum=5 //一共多少个图 例如6个请输入5
  $("#leftbtn").click(function(){
    var temp_href=$("#topface li:eq(0) a").attr("href");
    var temp_img=$("#topface li:eq(0) img").attr("src");
    var temp_h3=$("#topface li:eq(0) h3").html();
    var temp_p=$("#topface li:eq(0) p").html();

    for (i=0; i<countnum; i++){
      var n=i+1;
      $("#topface li:eq("+i+") a").attr('href',$("#topface li:eq("+n+") a").attr("href"));
      $("#topface li:eq("+i+") img").attr('src',$("#topface li:eq("+n+") img").attr("src"));
      $("#topface li:eq("+i+") h3").html($("#topface li:eq("+n+") h3").html());
      $("#topface li:eq("+i+") p").html($("#topface li:eq("+n+") p").html());
    };
    $("#topface li:eq("+countnum+") a").attr('href',temp_href);
    $("#topface li:eq("+countnum+") img").attr('src',temp_img);
    $("#topface li:eq("+countnum+") h3").html(temp_h3);
    $("#topface li:eq("+countnum+") p").html(temp_p);
  });
  $("#rightbtn").click(function(){
    var temp_href=$("#topface li:eq("+countnum+") a").attr("href");
    var temp_img=$("#topface li:eq("+countnum+") img").attr("src");
    var temp_h3=$("#topface li:eq("+countnum+") h3").html();
    var temp_p=$("#topface li:eq("+countnum+") p").html();

    for (i=countnum; i>0; i--){
      var n=i-1;
      $("#topface li:eq("+i+") a").attr('href',$("#topface li:eq("+n+") a").attr("href"));
      $("#topface li:eq("+i+") img").attr('src',$("#topface li:eq("+n+") img").attr("src"));
      $("#topface li:eq("+i+") h3").html($("#topface li:eq("+n+") h3").html());
      $("#topface li:eq("+i+") p").html($("#topface li:eq("+n+") p").html());
    };
    $("#topface li:eq(0) a").attr('href',temp_href);
    $("#topface li:eq(0) img").attr('src',temp_img);
    $("#topface li:eq(0) h3").html(temp_h3);
    $("#topface li:eq(0) p").html(temp_p);
  });
});

