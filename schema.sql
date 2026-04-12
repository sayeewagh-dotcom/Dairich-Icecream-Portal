CREATE TABLE admin_users (
    id              SERIAL          PRIMARY KEY,
    name            VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,           -- bcrypt hash
    role            VARCHAR(20)     NOT NULL DEFAULT 'staff',
    created_at      TIMESTAMP       NOT NULL DEFAULT NOW(),
 
    CONSTRAINT admin_users_role_check
        CHECK (role IN ('superadmin', 'staff'))
);

CREATE TABLE products (
    id          SERIAL          PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    description TEXT,
    tag         VARCHAR(50),                            -- e.g. 'Bestseller', 'Exotic', 'Heritage'
    image_path  VARCHAR(255),                           -- e.g. 'images/kesar_pista.jpg'
    is_active   BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP       NOT NULL DEFAULT NOW()
);
INSERT INTO products (name, description, tag, is_active) VALUES
  ('Kesar Pista',   'Saffron infused cream with roasted pistachios.',        'Bestseller', TRUE),
  ('Black Current', 'Rich berry swirl with juicy current pieces.',            'Exotic',     TRUE),
  ('Chappan Bhog',  'A royal blend of 56 traditional ingredients.',           'Heritage',   TRUE),
  ('Badam Roasted', 'Rich roasted almond cream, deeply satisfying.',          'Premium',    TRUE),
  ('Anjeer Badam',  'Delicate fig and almond in every scoop.',                'Artisanal',  TRUE),
  ('Choco Chips',   'Smooth cream loaded with real chocolate chips.',         'Popular',    TRUE),
  ('Chocolate',     'Classic deep chocolate, rich and indulgent.',            'Classic',    TRUE),
  ('Strawberry',    'Fresh strawberry swirls in natural cream.',              'Fresh',      TRUE),
  ('Vanilla',       'Pure Madagascar vanilla in every creamy bite.',          'Classic',    TRUE),
  ('Chocobar',      'Crispy chocolate shell with creamy center.',             'Signature',  TRUE);

  CREATE TABLE enquiries (
    id              SERIAL          PRIMARY KEY,
    company_name    VARCHAR(150)    NOT NULL,
    contact_person  VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL,
    phone           VARCHAR(20),
    business_type   VARCHAR(50),                        -- e.g. 'Restaurant', 'Retail', 'Hotel'
    message         TEXT,
    status          VARCHAR(20)     NOT NULL DEFAULT 'new',
    submitted_at    TIMESTAMP       NOT NULL DEFAULT NOW(),
 
    CONSTRAINT enquiries_status_check
        CHECK (status IN ('new', 'reviewed', 'contacted', 'closed'))
);

CREATE INDEX idx_enquiries_status       ON enquiries (status);
CREATE INDEX idx_enquiries_email        ON enquiries (email);
CREATE INDEX idx_enquiries_submitted_at ON enquiries (submitted_at DESC);

CREATE TABLE enquiry_flavours (
    enquiry_id  INT     NOT NULL REFERENCES enquiries(id) ON DELETE CASCADE,
    product_id  INT     NOT NULL REFERENCES products(id)  ON DELETE RESTRICT,
    PRIMARY KEY (enquiry_id, product_id)
);

CREATE TABLE distributors (
    id              SERIAL          PRIMARY KEY,
    enquiry_id      INT             REFERENCES enquiries(id) ON DELETE SET NULL,
    company_name    VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,           -- bcrypt hash
    is_active       BOOLEAN         NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP       NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_distributors_enquiry ON distributors (enquiry_id);

CREATE TABLE orders (
    id              SERIAL          PRIMARY KEY,
    distributor_id  INT             NOT NULL REFERENCES distributors(id) ON DELETE RESTRICT,
    total_amount    DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    status          VARCHAR(20)     NOT NULL DEFAULT 'pending',
    order_date      TIMESTAMP       NOT NULL DEFAULT NOW(),
    notes           TEXT,
 
    CONSTRAINT orders_status_check
        CHECK (status IN ('pending', 'confirmed', 'processing', 'dispatched', 'delivered', 'cancelled'))
); 

CREATE INDEX idx_orders_distributor ON orders (distributor_id);
CREATE INDEX idx_orders_status      ON orders (status);
CREATE INDEX idx_orders_order_date  ON orders (order_date DESC);

CREATE TABLE order_items (
    id              SERIAL          PRIMARY KEY,
    order_id        INT             NOT NULL REFERENCES orders(id)   ON DELETE CASCADE,
    product_id      INT             NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
    quantity_boxes  INT             NOT NULL CHECK (quantity_boxes > 0),
    price_per_box   DECIMAL(10, 2)  NOT NULL CHECK (price_per_box >= 0)
);
 
CREATE INDEX idx_order_items_order   ON order_items (order_id);
CREATE INDEX idx_order_items_product ON order_items (product_id);
CREATE TABLE delivery (
    id              SERIAL          PRIMARY KEY,
    order_id        INT             NOT NULL UNIQUE REFERENCES orders(id) ON DELETE CASCADE,
    expected_date   DATE,
    delivered_date  DATE,
    status          VARCHAR(20)     NOT NULL DEFAULT 'pending',
    tracking_notes  TEXT,
 
    CONSTRAINT delivery_status_check
        CHECK (status IN ('pending', 'in_transit', 'out_for_delivery', 'delivered', 'failed'))
);
 
CREATE INDEX idx_delivery_order  ON delivery (order_id);
CREATE INDEX idx_delivery_status ON delivery (status);

CREATE TABLE feedback (
    id              SERIAL          PRIMARY KEY,
    distributor_id  INT             NOT NULL REFERENCES distributors(id) ON DELETE CASCADE,
    order_id        INT             NOT NULL REFERENCES orders(id)       ON DELETE CASCADE,
    rating          INT             NOT NULL CHECK (rating BETWEEN 1 AND 5),
    message         TEXT,
    submitted_at    TIMESTAMP       NOT NULL DEFAULT NOW()
);
 
CREATE INDEX idx_feedback_distributor ON feedback (distributor_id);
CREATE INDEX idx_feedback_order       ON feedback (order_id);