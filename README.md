# ass3

-- Why we chose to use World Trading Data API over Alpha Vantage --

We looked at both APIs and decided that it'd be a better idea to use the World Trading Data API for 
two reasons primarily: Firstly, it allows us to do 250 API calls a day whereas Alpha Vantage allowed 
for 5 a minute. This could be an issue for us because we need to call the API quite a few times while 
testing and some times, we realize we are missing a semi colon somewhere and we get an exception and 
wasted one of our API calls for that minute. It sounds minor but considering there were four of us working
on it at the same time, we thought this could become problematic and a time waster. We only ran out of 
API calls for the day with World Trading Data twice and what we did to deal with this was another team member
signed up and got a free key. After this, we never had any issues with using up our API calls. We could've done
the same thing with Alpha Vantage but then there's the actual JSON that the 2 APIs return. We felt that the World
Trading Data had everything that we needed and was very easy to access but didn't see the same accessibility in 
the JSON returned by the example query on the Alpha Vantage website.

