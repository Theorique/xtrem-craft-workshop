## User stories

### Define Pivot Currency

> _**As**_ a Foreign Exchange Expert  
> _**I want to**_ be able to define a Pivot Currency   
> _**So**_ that I can express exchange rates based on it

```gherkin
Scenario Use Euro as Pivot Currency
    Given a Bank
    When I define define EUR as Pivot Currency
    Then The bank should use EUR as Pivot for conversion
```

### Add an exchange rate

> _**As**_ a Foreign Exchange Expert  
> _**I want to**_ add/update exchange rates by specifying: a multiplier rate and a currency   
> _**So**_ they can be used to evaluate client portfolios


```gherkin
Scenario Add an Exchange Rate for the Pivot Currency
    Given a Bank with EUR as Pivot Currency
    When I add an Exchange Rate for the Currency EUR
    Then I receive an error explaining that an Exchange Rate can not be added for the Pivot Currency
```

```gherkin
Scenario Add an invalid Rate for a Currency
    Given a Bank with EUR as Pivot Currency
    When I add an Exchange Rate of 0 for the Currency USD
    Then I receive an error explaining that an Exchange Rate should be greater than 0
```

```gherkin
Scenario Add an invalid Rate for a Currency
    Given a Bank with EUR as Pivot Currency
    When I add an Exchange Rate of -1 for the Currency USD
    Then I receive an error explaining that an Exchange Rate should be greater than 0
```

```gherkin
Scenario Add an Exchange Rate
    Given a Bank with EUR as Pivot Currency
    When I add an Exchange Rate of 1.298989888 for the Currency KRW
    Then it should succeed
```

```gherkin
Scenario Add an Exchange Rate
    Given a Bank with EUR as Pivot Currency
    When I add an Exchange Rate of 0.0000001455 for the Currency KRW
    Then it should succeed
```

```gherkin
Scenario Update an Exchange Rate
    Given a Bank with EUR as Pivot Currency
      And an Exchange Rate of 1.2 for Currency USD
    When I update an Exchange Rate for the Currency USD to 1.3
    Then the Exchange Rate from EUR to USD should be 1.3
```


### Convert a Money

> _**As**_ a Bank Consumer  
> _**I want to**_ convert a given amount in currency into another currency   
> _**So**_ it can be used to evaluate client portfolios


```gherkin
Scenario Convert into unknown currency 
  Given a Bank with EUR as Pivot Currency
  When I convert 10 EUR to Korean Wons
  Then I receive an error explaining that the system has no Exchange Rate defined for EUR->KRW
```

```gherkin
Scenario Convert into unknown currency 
  Given a Bank with EUR as Pivot Currency
    And an Exchange Rate of 1.2 for Currency USD
  When I convert 10 KRW to USD
  Then I receive an error explaining that the system has no Exchange Rate defined for KRW->USD
```

```gherkin
Scenario Convert 10 Euros in Euros in an American bank
  Given a Bank with USD as Pivot Currency
  When I convert 10 EUR to EUR
  Then I receive 10 EUR
```

```gherkin
Scenario Convert 10 Euros in Euros in an European bank
  Given a Bank with EUR as Pivot Currency
  When I convert 10 EUR to EUR
  Then I receive 10 EUR
```

```gherkin
Scenario Convert 10 Euros in Euros in an American bank
  Given a Bank with USD as Pivot Currency
  When I convert 10 EUR to EUR
  Then I receive 10 EUR
```

```gherkin
Scenario Convert from Pivot Currency
  Given a Bank with EUR as Pivot Currency
    And an exchange rate of 1.2 for Currency USD
  When I convert 10 EUR to USD
  Then I receive 12 USD
```

```gherkin
Scenario Convert to Pivot Currency
    Given a Bank with EUR as Pivot Currency
      And an exchange rate of 1.2 for Currency USD
    When I convert 12 USD to EUR
    Then I receive 10 EUR
```

```gherkin
Scenario Convert through Pivot Currency
    Given a Bank with EUR as Pivot Currency
      And an exchange rate of 1.2 for Currency USD
      And an exchange rate of 1344 for Currency KRW
    When I convert 10 USD to KRW
    Then I receive 11200 KRW
```

```gherkin
Scenario Round Tripping with same Currency
  Given a Bank with EUR as Pivot Currency
    And an Exchange Rate of 0.12548966 to KRW
  When I convert 10.254 KRW to KRW
    And I convert the money received back to KRW
  Then I receive 10.254 KRW
```

```gherkin
Scenario Round Tripping with different Currencies
  Given a Bank with EUR as Pivot Currency
    And an Exchange Rate of 1.235944 to USD
    And an Exchange Rate of 0.12548966 to KRW
  When I convert 10.254 KRW to USD
    And I convert the money received back to KRW
  Then I receive 10.254 KRW with 1% Tolerance
```


## Ubiquitous Language

### Portfolio

> Aggregation for a list of Moneys in various Currencies

### Pivot Currency 

> The currency through which a Bank calculates the other currency values 

### Exchange Rate

> Rate used to convert Money from a Pivot Currency to another Currency

