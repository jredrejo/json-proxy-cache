# json-proxy-cache
Wordpress plugin to cache api requests to a third server
Specially thought to cache YouTube api requests to be able to keep the quotas from the free layer

As an example, setting a 10 minutes cache will allow unlimited requests to this endpoint:

`/wp-json/jr/v1/proxy`

with the response from
`https://www.googleapis.com/youtube/v3/activities?part=snippet&channelId=UCWxgBGkDsd3lEhMjWfjqneQ&maxResults=10&key=<your-key>`

