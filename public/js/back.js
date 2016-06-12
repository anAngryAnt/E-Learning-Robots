/**
 * Created by 24203 on 2016/6/10.
 */

var h=0;var m=0;var s=0;
h=checkTime(h);
m=checkTime(m);
s=checkTime(s);
document.getElementById('time').innerHTML=h+":"+m+":"+s;
t=setTimeout('startTime()',500);
function startTime()
{
    s++;
    s=checkTime(s);

    if(s==60){
        m++;
        s=0;
        s=checkTime(s);
        m=checkTime(m);
    }
    if(m==60){
        h++;
        m=0;
        h=checkTime(h);
        m=checkTime(m);
    }
    document.getElementById('time').innerHTML=h+":"+m+":"+s;
    t=setTimeout('startTime()',1000);
}

function checkTime(i)
{
    if (i<10)
    {i="0" + i}
    return i;
}
