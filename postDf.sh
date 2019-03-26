#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
step=20
for (( i = 0; i < 60; i=(i+step) )); do
curl -sS --connect-timeout 10 -m 60 'http://juhe.nutbe.cn/Pay_Repost_postDf.html'
echo "----------------------------------------------------------------------------"
endDate=`date +"%Y-%m-%d %H:%M:%S"`
echo "â˜…[$endDate] Successful"
echo "----------------------------------------------------------------------------"
sleep $step
done
exit 0 
echo "----------------------------------------------------------------------------"
endDate=`date +"%Y-%m-%d %H:%M:%S"`
echo "★[$endDate] Successful"
echo "----------------------------------------------------------------------------"

