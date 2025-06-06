--drop table Cleck_User
DROP TABLE Cleck_User cascade constraints;
--create table Cleck_User
CREATE TABLE Cleck_User(
    user_id INTEGER not null,
    first_name VARCHAR2(100),
    last_name VARCHAR2(100),
    user_address VARCHAR2(100),
    user_email VARCHAR2(100),
    user_age INTEGER,
    user_gender CHARACTER(8),
    user_password VARCHAR2(100),
    user_profile_picture VARCHAR2(255),
    user_type VARCHAR2(10),
    user_contact_no INTEGER
);

--add primary key constraint for user_id in Cleck_User table 
ALTER TABLE Cleck_User
ADD CONSTRAINT PK_USER_ID PRIMARY KEY (user_id);

ALTER TABLE Cleck_User
ADD user_dob DATE;

--create sequence for primary key 
DROP SEQUENCE SEQ_USER;
CREATE SEQUENCE SEQ_USER 
START WITH 1
INCREMENT BY 1
 NOCACHE
 NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER user_trg
BEFORE INSERT ON Cleck_User
FOR EACH ROW
BEGIN 
  IF :NEW.user_id IS NULL THEN 
  SELECT seq_user.NEXTVAL INTO :NEW.user_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table trader
DROP TABLE TRADER cascade constraints;
--create table trader
CREATE TABLE TRADER(
    trader_id INTEGER not null,
    trader_type VARCHAR2(100),
    shop_name VARCHAR2(100),
    verification_status NUMBER(1),
    user_id INTEGER,
    profile_picture VARCHAR2(255),
    verification_code NUMBER,
    VERFIED_ADMIN NUMBER,
    verification_send NUMBER
);
--add foreign key constraint user_id in trader table
ALTER TABLE TRADER
ADD CONSTRAINT FK_TRADER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User (user_id)
ON DELETE CASCADE;

--add primary key constraint trader_id in trader table
ALTER TABLE TRADER
ADD CONSTRAINT PK_TRADER_ID PRIMARY KEY (trader_id);

--create sequence for primary key 
DROP SEQUENCE SEQ_TRADER;
CREATE SEQUENCE SEQ_TRADER START WITH 200
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER trader_trg
BEFORE INSERT ON TRADER
FOR EACH ROW
BEGIN 
  IF :NEW.trader_id IS NULL THEN 
  SELECT seq_trader.NEXTVAL INTO :NEW.trader_id FROM SYS.DUAL;
  END IF;
END;
/



--drop table customer
DROP TABLE CUSTOMER cascade constraints;
--create table customer
CREATE TABLE CUSTOMER(
    customer_id INTEGER not null,
    customer_date_joined DATE,
    verification_code INTEGER,
    date_updated DATE,
    verified_customer NUMBER(1),
    user_id INTEGER,
    profile_picture VARCHAR2(255)
);
--add foreign key constraint user_id in customer table
ALTER TABLE CUSTOMER
ADD CONSTRAINT FK_CUSTOMER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;

--add primary key constraint customer_id in customer table
ALTER TABLE CUSTOMER
ADD CONSTRAINT PK_CUSTOMER_ID PRIMARY KEY (customer_id);

--create sequence for primary key 
DROP SEQUENCE SEQ_CUSTOMER;
CREATE SEQUENCE SEQ_CUSTOMER 
START WITH 300  
INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER customer_trg
BEFORE INSERT ON CUSTOMER
FOR EACH ROW
BEGIN 
  IF :NEW.customer_id IS NULL THEN 
  SELECT seq_customer.NEXTVAL INTO :NEW.customer_id FROM SYS.DUAL;
  END IF;
END;
/


--drop table admin
DROP TABLE ADMIN cascade constraints;
--create table admin
CREATE TABLE ADMIN(
    admin_id INTEGER not null,
    user_id INTEGER
);
--add foreign key constraint user_id in admin table
ALTER TABLE ADMIN
ADD CONSTRAINT FK_ADMIN_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;

--add primary key constraint admin_id in admin table
ALTER TABLE ADMIN
ADD CONSTRAINT PK_ADMIN_ID PRIMARY KEY (admin_id);

--create sequence for primary key 
DROP SEQUENCE SEQ_ADMIN;
CREATE SEQUENCE SEQ_ADMIN 
  START WITH 400
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER admin_trg
BEFORE INSERT ON ADMIN
FOR EACH ROW
BEGIN 
  IF :NEW.admin_id IS NULL THEN 
  SELECT seq_admin.NEXTVAL INTO :NEW.admin_id FROM SYS.DUAL;
  END IF;
END;
/



--drop table shop
DROP TABLE SHOP cascade constraints;
--create table shop 
CREATE TABLE SHOP(
    shop_id INTEGER not null,
    shop_name VARCHAR2(100),
    shop_description VARCHAR2(500),
    user_id INTEGER,
    verified_shop NUMBER(1),
    shop_profile varchar2(255),
    shop_category_id NUMBER,
    registration_no NUMBER
);
--add foreign key constraint user_id in shop table 
ALTER TABLE SHOP
ADD CONSTRAINT FK_SHOP_USER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;
--add primary key constraint shop_id in shop table 
ALTER TABLE SHOP 
ADD CONSTRAINT PK_SHOP_ID PRIMARY KEY (shop_id);

--create sequence for primary key 
DROP SEQUENCE SEQ_SHOP;
CREATE SEQUENCE SEQ_SHOP START WITH 500
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER shop_trg
BEFORE INSERT ON SHOP
FOR EACH ROW
BEGIN 
  IF :NEW.shop_id IS NULL THEN 
  SELECT seq_shop.NEXTVAL INTO :NEW.shop_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table product
DROP TABLE PRODUCT cascade constraints;
--create table product
CREATE TABLE PRODUCT(
    product_id INTEGER not null,
    product_name VARCHAR2(100),
    product_description VARCHAR2(500),
    product_price INTEGER,
    product_quantity INTEGER,
    stock_available VARCHAR2(100),
    is_disabled NUMBER,
    max_order INTEGER,
    allergy_information VARCHAR2(300),
    product_picture VARCHAR2(255),
    product_added_date DATE,
    product_update_date DATE,
    category_id INTEGER not null,
    user_id INTEGER,
    ADMIN_VERIFIED NUMBER
);
--add primary key constraint product_id in product table 
ALTER TABLE PRODUCT
ADD CONSTRAINT PK_PRODUCT_ID PRIMARY KEY (product_id);
--add foreign key constraint category_id in product table
ALTER TABLE PRODUCT 
ADD CONSTRAINT FK_CATEGORY_ID FOREIGN KEY (category_id) REFERENCES PRODUCT_CATEGORY(category_id)
ON DELETE CASCADE;
--add foreign key constraint user_id in product table
ALTER TABLE PRODUCT 
ADD CONSTRAINT FK_USER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_PRODUCT;
CREATE SEQUENCE SEQ_PRODUCT START WITH 600
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER product_trg
BEFORE INSERT ON PRODUCT
FOR EACH ROW
BEGIN 
  IF :NEW.product_id IS NULL THEN 
  SELECT seq_product.NEXTVAL INTO :NEW.product_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table report
DROP TABLE REPORT cascade constraints;
--create table report
CREATE TABLE REPORT(
    report_id INTEGER not null,
    report_type VARCHAR2(100),
    report_name VARCHAR2(100),
    report_date DATE,
    product_id INTEGER not null,
    user_id INTEGER
);
--add primary key constraint in report table
ALTER TABLE REPORT
ADD CONSTRAINT PK_REPORT_ID PRIMARY KEY (report_id);
--add foreign key constraint user_id in report table
ALTER TABLE REPORT
ADD CONSTRAINT FK_REPORT_USER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;
--add foreign key product_id in report table
ALTER TABLE REPORT
ADD CONSTRAINT FK_REPORT_PRODUCT_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_REPORT;
CREATE SEQUENCE SEQ_REPORT START WITH 700
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER report_trg
BEFORE INSERT ON REPORT
FOR EACH ROW
BEGIN 
  IF :NEW.report_id IS NULL THEN 
  SELECT seq_report.NEXTVAL INTO :NEW.report_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table review
DROP TABLE REVIEW cascade constraints;
--create table review
CREATE TABLE REVIEW(
    review_id INTEGER not null,
    review_date DATE,
    review_score INTEGER,
    feedback VARCHAR2(500),
    product_id INTEGER not null,
    user_id INTEGER
);
--add primary key constraint review_id in review table
ALTER TABLE REVIEW
ADD CONSTRAINT PK_REVIEW_ID PRIMARY KEY (review_id);
--add foreign key constraint user_id in review table
ALTER TABLE REVIEW
ADD CONSTRAINT FK_REVIEW_USER_ID FOREIGN KEY (user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;
--add foreign key constraint product_id in review table
ALTER TABLE REVIEW 
ADD CONSTRAINT FK_REVIEW_PRODUCT_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;
--add column review_provided in review table
ALTER TABLE REVIEW
ADD REVIEW_PROCIDED NUMBER;
--add column order_id in review table
ALTER TABLE REVIEW
ADD order_id NUMBER NOT NULL;
--add column customer_id in review table
ALTER TABLE REVIEW
ADD customer_id NUMBER NOT NULL;
--add foreign key constraint order_product_id in review table
ALTER TABLE REVIEW 
ADD CONSTRAINT FK_REVIEW_ORDER_ID FOREIGN KEY (order_id) REFERENCES ORDER_PRODUCT(order_product_id)
ON DELETE CASCADE;
--add foreign key constraint customer_id in review table
ALTER TABLE REVIEW
ADD CONSTRAINT FK_REVIEW_CUSTOMER_ID FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id)
ON DELETE CASCADE;


--create sequence for primary key 
DROP SEQUENCE SEQ_REVIEW;
CREATE SEQUENCE SEQ_REVIEW START WITH 800
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER review_trg
BEFORE INSERT ON REVIEW
FOR EACH ROW
BEGIN 
  IF :NEW.review_id IS NULL THEN 
  SELECT seq_review.NEXTVAL INTO :NEW.review_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table cart
DROP TABLE CART cascade constraints;
--create table cart 
CREATE TABLE CART (
    cart_id INTEGER not null,
    customer_id INTEGER not null,
    order_product_id INTEGER not null
);
--add primary key cart_id in cart table 
ALTER TABLE CART 
ADD CONSTRAINT PK_CART_ID PRIMARY KEY (cart_id);
--add foreign key constraint customer_id in cart table
ALTER TABLE CART 
ADD CONSTRAINT FK_CUSTOMER_CART_ID FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id)
ON DELETE CASCADE;
--add foreign key constraint order_product_id in cart table
ALTER TABLE CART 
ADD CONSTRAINT FK_ORDER_PRODUCT_CART_ID FOREIGN KEY (order_product_id) REFERENCES ORDER_PRODUCT(order_product_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_CART;
CREATE SEQUENCE SEQ_CART START WITH 900
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER cart_trg
BEFORE INSERT ON CART
FOR EACH ROW
BEGIN 
  IF :NEW.cart_id IS NULL THEN 
  SELECT seq_cart.NEXTVAL INTO :NEW.cart_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table wishlist 
DROP TABLE WISHLIST cascade constraints;
--create table wishlist 
CREATE TABLE WISHLIST(
    wishlist_id INTEGER not null,
    wishlist_created_date DATE,
    wishlist_updated_date DATE,
    customer_id INTEGER not null
);
--add primary key constraint wishlist_id in wishlist table
ALTER TABLE WISHLIST
ADD CONSTRAINT PK_WISHLIST_ID PRIMARY KEY (wishlist_id);
--add foreign key constraint customer_id in wishlist table
ALTER TABLE WISHLIST 
ADD CONSTRAINT FK_CUSTOMER_WISHLIST_ID FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_WISHLIST;
CREATE SEQUENCE SEQ_WISHLIST START WITH 1000
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER wishlist_trg
BEFORE INSERT ON WISHLIST
FOR EACH ROW
BEGIN 
  IF :NEW.wishlist_id IS NULL THEN 
  SELECT seq_wishlist.NEXTVAL INTO :NEW.wishlist_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table product_category
DROP TABLE PRODUCT_CATEGORY cascade constraints;
--create table product_category
CREATE TABLE PRODUCT_CATEGORY(
    category_id INTEGER not null,
    category_type VARCHAR2(100)
);
--add primary key constraint category_id in product_category table
ALTER TABLE PRODUCT_CATEGORY
ADD CONSTRAINT PK_CATEGORY_ID PRIMARY KEY (category_id);

--add column in product_category
ALTER TABLE PRODUCT_CATEGORY
ADD category_image VARCHAR2(255);

--create sequence for primary key 
DROP SEQUENCE SEQ_CATEGORY;
CREATE SEQUENCE SEQ_CATEGORY START WITH 1100
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER category_trg
BEFORE INSERT ON PRODUCT_CATEGORY
FOR EACH ROW
BEGIN 
  IF :NEW.category_id IS NULL THEN 
  SELECT seq_category.NEXTVAL INTO :NEW.category_id FROM SYS.DUAL;
  END IF;
END;
/


--drop table discount
DROP TABLE DISCOUNT cascade constraints;
--create table discount
CREATE TABLE DISCOUNT(
    discount_id INTEGER not null,
    discount_occassion VARCHAR2(100),
    discount_percent VARCHAR2(10),
    product_id INTEGER not null

);
--add primary key constraint discount_id in discount table
ALTER TABLE DISCOUNT
ADD CONSTRAINT PK_DISCOUNT_ID PRIMARY KEY (discount_id);
--add foreign key constraint product_id in discount table
ALTER TABLE DISCOUNT 
ADD CONSTRAINT FK_PRODUCT_DISCOUNT_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_DISCOUNT;
CREATE SEQUENCE SEQ_DISCOUNT START WITH 1200
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER discount_trg
BEFORE INSERT ON DISCOUNT
FOR EACH ROW
BEGIN 
  IF :NEW.discount_id IS NULL THEN 
  SELECT seq_discount.NEXTVAL INTO :NEW.discount_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table payment
DROP TABLE PAYMENT cascade constraints;
--create table payment
CREATE TABLE PAYMENT(
    payment_id INTEGER not null,
    payment_date DATE,
    payment_type VARCHAR2(100),
    payment_amount INTEGER,
    customer_id INTEGER not null,
    order_product_id INTEGER not null
);
--add primary key constraint payment_id in payment table
ALTER TABLE PAYMENT 
ADD CONSTRAINT PK_PAYMENT_ID PRIMARY KEY (payment_id);
--add foreign key constraint customer_id in payment table
ALTER TABLE PAYMENT 
ADD CONSTRAINT FK_PAYMENT_CUSTOMER_ID FOREIGN KEY (customer_id) REFERENCES CUSTOMER(customer_id)
ON DELETE CASCADE;
--add foreign key constraint order_product_id in payment table
ALTER TABLE PAYMENT 
ADD CONSTRAINT FK_PAYMENT_ORDER_PRODUCT_ID FOREIGN KEY (order_product_id) REFERENCES ORDER_PRODUCT(order_product_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_PAYMENT;
CREATE SEQUENCE SEQ_PAYMENT START WITH 1300
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER payment_trg
BEFORE INSERT ON PAYMENT
FOR EACH ROW
BEGIN 
  IF :NEW.payment_id IS NULL THEN 
  SELECT seq_payment.NEXTVAL INTO :NEW.payment_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table order_product
DROP TABLE ORDER_PRODUCT cascade constraints;
--create table ordera_product
CREATE TABLE ORDER_PRODUCT(
   	order_product_id INTEGER NOT NULL, 
	no_of_product NUMBER, 
	order_status NUMBER, 
	total_price NUMBER, 
	slot_id INTEGER not null,
    cart_id INTEGER not null,
	order_date DATE, 
	order_time TIMESTAMP(6), 
	discount_amount NUMBER, 
	customer_id NUMBER
);
--add primary key constraint order_product_id in order_product table
ALTER TABLE ORDER_PRODUCT
ADD CONSTRAINT PK_ORDER_PRODUCT_ID PRIMARY KEY (order_product_id);
--add foreign key constraint slot_id in order_product table
ALTER TABLE ORDER_PRODUCT 
ADD CONSTRAINT FK_SLOT_ID FOREIGN KEY (slot_id) REFERENCES COLLECTION_SLOT(slot_id)
ON DELETE CASCADE;
--add foreign key constraint cart_id in order_product table
ALTER TABLE ORDER_PRODUCT 
ADD CONSTRAINT FK_CART_ID FOREIGN KEY (cart_id) REFERENCES CART(cart_id)
ON DELETE CASCADE;

--create sequence for primary key 
DROP SEQUENCE SEQ_ORDER;
CREATE SEQUENCE SEQ_ORDER START WITH 1400
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER order_trg
BEFORE INSERT ON ORDER_PRODUCT
FOR EACH ROW
BEGIN 
  IF :NEW.order_product_id IS NULL THEN 
  SELECT seq_order.NEXTVAL INTO :NEW.order_product_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table order_details
DROP TABLE ORDER_DETAILS cascade constraints;
--create table order_details
CREATE TABLE ORDER_DETAILS(
    product_id NUMBER,  
    trader_user_id NUMBER,
    order_product_id INTEGER,
    product_qty NUMBER,
    product_price NUMBER
);

--add foreign key constraint order_product_id in order_tables table
ALTER TABLE ORDER_DETAILS 
ADD CONSTRAINT FK_ORDER_PRODUCT_DETAILS_ID FOREIGN KEY (order_product_id) REFERENCES ORDER_PRODUCT(order_product_id);
--add foreign key constraint product_id in order_product table
ALTER TABLE ORDER_DETAILS 
ADD CONSTRAINT FK_PRODUCT_DETAILS_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;
--add foreign key constraint user_id in order_details table
ALTER TABLE ORDER_DETAILS
ADD CONSTRAINT FK_TRADER_USER_ID FOREIGN KEY (trader_user_id) REFERENCES Cleck_User(user_id)
ON DELETE CASCADE;

--drop table collection_slot
DROP TABLE COLLECTION_SLOT cascade constraints;
--create table collection_slot
CREATE TABLE COLLECTION_SLOT(
    slot_id INTEGER not null,
    slot_date DATE,
    slot_time varchar2(255),
    slot_day VARCHAR2(10),
    order_product_id INTEGER
);
--add primary key constraint slot_id in collection_slot table
ALTER TABLE COLLECTION_SLOT
ADD CONSTRAINT PK_COLLECTION_SLOT PRIMARY KEY (slot_id);
--add foreign key constraint order_product_id in collection_slot table
ALTER TABLE COLLECTION_SLOT
ADD CONSTRAINT FK_ORDER_PRODUCT_ID FOREIGN KEY (order_product_id) REFERENCES ORDER_PRODUCT(order_product_id)
ON DELETE CASCADE;

--add column in collection_slot
ALTER TABLE COLLECTION_SLOT
ADD location VARCHAR2(255);

--create sequence for primary key 
DROP SEQUENCE SEQ_SLOT;
CREATE SEQUENCE SEQ_SLOT START WITH 1500
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;
--create trigger
COMMIT;
CREATE OR REPLACE TRIGGER slot_trg
BEFORE INSERT ON collection_slot
FOR EACH ROW
BEGIN 
  IF :NEW.slot_id IS NULL THEN 
  SELECT seq_slot.NEXTVAL INTO :NEW.slot_id FROM SYS.DUAL;
  END IF;
END;
/

--drop table cart_item
DROP TABLE CART_ITEM cascade constraints;
--create table cart_item
CREATE TABLE CART_ITEM(
    no_of_products INTEGER,
    cart_id INTEGER not null,
    product_id INTEGER not null,
    product_price NUMBER
);
--add foreign key constraint cart_id in cart_item table
ALTER TABLE CART_ITEM 
ADD CONSTRAINT FK_CART_ITEM_ID FOREIGN KEY (cart_id) REFERENCES CART(cart_id)
ON DELETE CASCADE;
--add foreign key constraint product_id in cart_item table
ALTER TABLE CART_ITEM 
ADD CONSTRAINT FK_PRODUCT_CART_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;

--drop tabel wishlist_item
DROP TABLE WISHLIST_ITEM cascade constraints;
--create table wishlist_item
CREATE TABLE WISHLIST_ITEM(
    wishlist_id INTEGER not null,
    product_id INTEGER not null
);
--add foreign key constraint wishlist_id in wishlist_item table
ALTER TABLE WISHLIST_ITEM
ADD CONSTRAINT FK_WISHLIST_ITEM_ID FOREIGN KEY (wishlist_id) REFERENCES WISHLIST(wishlist_id)
ON DELETE CASCADE;
--add foreign key constraint product_id in wishlist_item table
ALTER TABLE WISHLIST_ITEM
ADD CONSTRAINT FK_PRODUCT_WISHLIST_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;

--drop table product_report
DROP TABLE PRODUCT_REPORT cascade constraints;
--create table product_report
CREATE TABLE PRODUCT_REPORT(
    report_id INTEGER not null,
    product_id INTEGER not null
);
--add foreign key constraint report_id in product_report table
ALTER TABLE PRODUCT_REPORT
ADD CONSTRAINT FK_REPORT_ID FOREIGN KEY (report_id) REFERENCES REPORT(report_id)
ON DELETE CASCADE;
--add foreign key constraint product_id in product_report table
ALTER TABLE PRODUCT_REPORT 
ADD CONSTRAINT FK_PRODUCT_ID FOREIGN KEY (product_id) REFERENCES PRODUCT(product_id)
ON DELETE CASCADE;

-- Drop the table if it exists
DROP TABLE CONTACTUS CASCADE CONSTRAINTS;

-- Create the sequence to generate unique query_id values
DROP SEQUENCE contact_id_seq;
CREATE SEQUENCE contact_id_seq
  START WITH 01
  INCREMENT BY 1
  NOCACHE
  NOCYCLE;

-- Create the CONTACTUS table
CREATE TABLE CONTACTUS (
    query_id NUMBER(10) NOT NULL,
    first_name VARCHAR2(100),
    last_name VARCHAR2(100),
    email VARCHAR2(100),
    contact_no VARCHAR2(20),
    subject VARCHAR2(255),
    message VARCHAR2(1000),
    verified_status NUMBER(1) DEFAULT 0
);

-- Alter the table to add a primary key constraint
ALTER TABLE CONTACTUS
ADD CONSTRAINT PK_QUERY_ID PRIMARY KEY (query_id);

-- Create the trigger to populate query_id automatically
CREATE OR REPLACE TRIGGER contact_id_trigger
BEFORE INSERT ON CONTACTUS
FOR EACH ROW
BEGIN
  IF :NEW.query_id IS NULL THEN
    SELECT contact_id_seq.NEXTVAL INTO :NEW.query_id FROM DUAL;
  END IF;
END;
/

-- Commit the changes
COMMIT;
