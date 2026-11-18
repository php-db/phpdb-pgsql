DROP TABLE IF EXISTS test_child;
DROP TABLE IF EXISTS test;
CREATE TABLE IF NOT EXISTS test (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL
);

INSERT INTO test (name, value) 
VALUES
('foo', 'bar'),
('bar', 'baz');

DROP TABLE IF EXISTS test_charset;
CREATE TABLE IF NOT EXISTS test_charset (
    id SERIAL PRIMARY KEY,
    field$ VARCHAR(255) NOT NULL,
    field_ VARCHAR(255) NOT NULL
);

INSERT INTO test_charset (field$, field_) 
VALUES
('foo', 'bar'),
('bar', 'baz');

CREATE TABLE IF NOT EXISTS test_seq (
    id SERIAL,
    foo VARCHAR(255) NOT NULL,
    CONSTRAINT test_seq_pkey PRIMARY KEY (id)
);

DROP TABLE IF EXISTS test_connection;
CREATE TABLE IF NOT EXISTS test_connection (
    id SERIAL PRIMARY KEY,
    label VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS test_child (
    id SERIAL PRIMARY KEY,
    test_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    CONSTRAINT test_child_test_id_fkey FOREIGN KEY (test_id) REFERENCES test (id) ON DELETE CASCADE,
    CONSTRAINT test_child_quantity_check CHECK (quantity > 0)
);

CREATE OR REPLACE FUNCTION test_child_touch() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    RETURN NEW;
END;
$$;

CREATE TRIGGER test_child_before_insert
    BEFORE INSERT ON test_child
    FOR EACH ROW EXECUTE FUNCTION test_child_touch();
