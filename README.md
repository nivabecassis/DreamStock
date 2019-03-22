# DREAM STOCK 

Welcome to the Dream Stock Web Application! Our goal is to offer a simple way of viewing, purchasing, and selling stocks available in real time. 

If you're new to our services please feel free to look around and read the documentation or simply follow the link below to try our application.

We hope you will enjoy using our Web Application! Any feedback is always appreciated!

http://dreamstock.herokuapp.com/

## Instructions

If you are a new user, press the 'Join for free' button to register yourself in our systems. If you have already created an account, press the 'Log in' button. You will be prompted to enter basic credentials that will be used to authenticate yourself on every session.

Once you have registered you will reach your portfolio page. This is where you will view any owned stocks that you have purchased along with a quotes section to research other stocks. You will also see some basic information about your profile including your balance and portfolio's current value. 

Each of your stocks will have a 'Buy' and 'Sell' option that allows you perform a transaction on your stock. Upon selection, your page is re-loaded with a new section that allows you to buy or sell a number of stocks for the selected company. 

## Currency

For the sake of simplicity, we have decided to keep all prices are in USD. Values queried from the APIs are converted into USD.

## APIs

This application uses two APIs for querying market data in realtime.

- Free Forex API https://www.freeforexapi.com/
- World Trading Data API https://www.worldtradingdata.com/

We also had the option of using the Alpha Vantage API however, we decided to use the World Trading Data API instead. We looked at both APIs and decided that it'd be a better idea to use the World Trading Data API for two reasons primarily: Firstly, it allows us to do 250 API calls a day whereas Alpha Vantage allowed for 5 a minute. 

This could be an issue for us because we need to call the API quite a few times while testing and some times, we realize we are missing a semi colon somewhere and we get an exception and wasted one of our API calls for that minute. It sounds minor but considering there were four of us working
on it at the same time, we thought this could become problematic and a time waster. We only ran out of API calls for the day with World Trading Data twice and what we did to deal with this was another team member signed up and got a free key. After this, we never had any issues with using up our API calls. We could've done the same thing with Alpha Vantage but then there's the actual JSON that the 2 APIs return. We felt that the World Trading Data had everything that we needed and was very easy to access but didn't see the same accessibility in the JSON returned by the example query on the Alpha Vantage website.

## Database & Tables

Three tables are used to build this stock portfolio finance website:  
> 1. users
> 2. portfolios
> 3. portfolio_stocks

The relationship between the tables are as the following details below:

> * users <-- 1:1 --> portfolios (1 to 1).
> * portfolios -- 1:* --> portfolio_stocks (1 to many).
___

| users             | type         |
|-------------------|--------------|
| id                | int          |
| name              | string       |
| email             | string       |
| email_verified_at | timestamp    |
| password          | string       |
| remember_token    | varchar(100) |
| created_at        | timestamp    |
| updated_at        | timestamp    |
___

| portfolios | type           |
|------------|----------------|
| id         | int            |
| user_id    | int            |
| cash_owned | decimal(10, 2) |

___

| portfolio_stocks | type           |
|------------------|----------------|
| id               | int            |
| ticker_symbol    | string         |
| portfolio_id     | int            |
| share_count      | int            |
| purchase_date    | timestamp      |
| purchase_price   | decimal(10, 2) |
| weighted_price   | decimal(10, 2) |


