# Converge Payments

This module integrate the payment gateway [Evalon Converge Payments](https://www.elavon.com/). For now only the card purchase is developed.

## Installation

### Manually

* Copy the module into ```<thelia_root>/local/modules/``` directory and be sure that the name of the module is ConvergePayments.
* Activate it in your thelia administration panel

### Composer

Add it in your main thelia composer.json file

```
composer require your-vendor/converge-payments-gateway-module:~1.0
```

## Usage

Once activated, click on the configure button, and enter the required information.

During the test phase, you can define the IP addresses allowed to use the Atos module on the front office, so that your customers will not be able to pay with ConvergePayments during this test phase.

A log of Converge post-payment callbacks is displayed in the configuration page.

## Hook

### order-payment-gateway.body

Used for displaying the card information form. Here you customer will enter his credit card credentials like the card number, expiration date, etc.

### order-edit.cart-bottom

In the back-office for displaying information

## Loop

#### Input arguments

|Argument |Description |
|---      |--- |
|**id**   | A single or a list of ids. |
|**order_id** | A single order id. |

#### Output arguments

|Variable       |Description |
|---            |--- |
|$ID            | The ConvergePayments id |
|$ORDER_ID      | the order id related to the payment |
|$MESSAGE_ID    | the message id returned by ConvergePayments API |
|$MESSAGE       | the message returned by ConvergePayments API |

### Exemple

    {loop name="converge" type="converge-payments" order_id=$order_id}
    <div class="table-responsive">
        <table class="table table-striped table-condensed table-left-aligned">
            <caption class="clearfix">
                {intl l='Converge Payments information' d="convergepaymentsgateway"}
            </caption>
            <tbody>
            <tr>
                <th>{intl l='Code' d="convergepaymentsgateway"}</th>
                <td>{$MESSAGE_ID}</td>
            </tr>
            <tr>
                <th>{intl l='Message' d="convergepaymentsgateway"}</th>
                <td>{$MESSAGE}</td>
            </tr>
            </tbody>
        </table>
    </div>
    {/loop}


