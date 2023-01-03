<h1 align="center">Backend Code Challenge (lite version)</h1>

# Description

Your task is to build a mini-aspire API:

It is an app that allows authenticated users to go through a loan application. It doesn’t have to contain too many fields, but at least "amount required" and "loan term". All the loans will be assumed to have a “weekly” repayment frequency.

After the loan is approved, the user must be able to submit the weekly loan repayments. It can be a simplified repay functionality, which won’t need to check if the dates are correct but will just set the weekly amount to be repaid.

# Features

- Register user (Both admin (This is a test application so allow to register admin user) ,customer)
- Login user
- Customer can create loan
- Customer can do the payment of schedule payment loans
- Customer can see his/own loans (with pagination by 10 rows)
- Admin can see all loans by order of latest one (with pagination by 10 rows)
- Admin can approve the loan
- User can get the detail of loan with schedule payments (Only his own loans)
- Admin can get the detail of loan with schedule payments

# Postman API

https://www.postman.com/dhaval7790/workspace/mini-aspire/folder/2552036-3f1a2070-ec21-4f3e-b4d2-56b6afe6a1d9?ctx=documentation

Here in environment section global section please update your base url "BASE_URL" and after login please set the token "TOKEN"

# Setup environment in local

- Clone the repository
- composer install
- copy .env.example to .env (Update your database setting)
- php artisan key:generate
- php artisan migrate
- php artisan module:seed

# Project structure

I have used Module based project structure which can easily help me to reuse any modules. As you can find modules inside the "Modules" folder

# Feature tests and Unit tests

- Run the `php artisan test` to run all unit test or if you want to run specific test `php artisan test --filter <Name of Test>`

Here are list of <b>Unit</b> tests

| Name | Description | Command  |
|---|---|---|
| test_loan_create | Here this unit test help me to validate my repository function of loan create | `php artisan test --filter test_loan_create` |
| test_fail_loan_create | Here this unit test help me to validate my repository function of loan create with some invalid argument | `php artisan test --filter test_fail_loan_create` |
| test_loan_with_payment | Here this unit test help me to validate my repository function of payment | `php artisan test --filter test_loan_with_payment` |
| test_fail_with_loan_with_payment | Here this unit test help me to validate my repository function of payment with some invalid argument | `php artisan test --filter test_loan_with_payment` |


Here are list of <b>Feature</b> tests

| Name | Description | Command  |
|---|---|---|
| test_user_register | Register new user | `php artisan test --filter test_user_register` |
| test_user_login | Login user | `php artisan test --filter test_user_login` |
| test_list_loan_by_customer | Test as customer to list of loans | `php artisan test --filter test_list_loan_by_customer` |
| test_list_loan_by_admin | Test as admin to list of loans | `php artisan test --filter test_list_loan_by_admin` |
| test_create_loan_by_customer | Create loan act as customer | `php artisan test --filter test_create_loan_by_customer` |
| test_create_approve_loan_by_admin | Approve loan act as admin | `php artisan test --filter test_create_approve_loan_by_admin` |
| test_create_approve_loan_by_customer | Approve loan act as customer | `php artisan test --filter test_create_approve_loan_by_customer` |
| test_loan_payment | Test the payment | `php artisan test --filter test_loan_payment` |
| test_multiple_payment_of_loan_schedule | Test the payment and validate to do same payment again | `php artisan test --filter test_multiple_payment_of_loan_schedule` |
| test_paid_loan_payment | Test the payment and complete all schedule payment to test loan paid or not | `php artisan test --filter test_paid_loan_payment` |
| test_create_loan_and_get | Create loan and get the list of loans | `php artisan test --filter test_create_loan_and_get` |