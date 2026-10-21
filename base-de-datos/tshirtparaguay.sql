-- Base de datos de t_shirtparaguay (PostgreSQL 13 o más nuevo)
-- Importala en una base vacía llamada tshirtparaguay (pgAdmin > Query Tool > abrir este archivo > Ejecutar).
-- Incluye: tablas, 15 productos de ejemplo, talles, colores, categorías, 4 slides y el usuario administrador.
-- Panel: admin@tshirtparaguay.com / admin123

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description character varying(255),
    image character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: colors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.colors (
    id bigint NOT NULL,
    name character varying(60) NOT NULL,
    hex character varying(7) DEFAULT '#cccccc'::character varying NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: colors_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.colors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: colors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.colors_id_seq OWNED BY public.colors.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: favorites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.favorites (
    user_id bigint NOT NULL,
    product_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: order_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.order_items (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    product_id bigint,
    product_variant_id bigint,
    product_name character varying(255) NOT NULL,
    size character varying(30),
    color character varying(60),
    price bigint NOT NULL,
    quantity integer NOT NULL,
    total bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.orders (
    id bigint NOT NULL,
    number character varying(20) NOT NULL,
    user_id bigint,
    seller_id bigint,
    channel character varying(10) DEFAULT 'web'::character varying NOT NULL,
    status character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    payment_method character varying(30) NOT NULL,
    payment_status character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    customer_name character varying(255) NOT NULL,
    customer_email character varying(255),
    customer_phone character varying(40),
    customer_document character varying(30),
    delivery_method character varying(10) DEFAULT 'envio'::character varying NOT NULL,
    address character varying(255),
    city character varying(80),
    notes text,
    subtotal bigint NOT NULL,
    discount bigint DEFAULT '0'::bigint NOT NULL,
    shipping bigint DEFAULT '0'::bigint NOT NULL,
    total bigint NOT NULL,
    cash_received bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: payments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    gateway character varying(30) NOT NULL,
    method character varying(30) NOT NULL,
    amount bigint NOT NULL,
    currency character varying(3) DEFAULT 'PYG'::character varying NOT NULL,
    status character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    transaction_id character varying(255),
    authorization_code character varying(255),
    card_brand character varying(30),
    card_last4 character varying(4),
    payload json,
    paid_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- Name: product_images; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_images (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    color_id bigint,
    path character varying(255) NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: product_images_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.product_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: product_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.product_images_id_seq OWNED BY public.product_images.id;


--
-- Name: product_variants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_variants (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    size_id bigint NOT NULL,
    color_id bigint,
    sku character varying(80),
    stock integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: product_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.product_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: product_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.product_variants_id_seq OWNED BY public.product_variants.id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.products (
    id bigint NOT NULL,
    category_id bigint,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description text,
    sku character varying(60),
    price bigint NOT NULL,
    compare_price bigint,
    is_active boolean DEFAULT true NOT NULL,
    is_featured boolean DEFAULT false NOT NULL,
    is_new boolean DEFAULT false NOT NULL,
    sold_count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.settings (
    key character varying(80) NOT NULL,
    value text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sizes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sizes (
    id bigint NOT NULL,
    name character varying(30) NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sizes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sizes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sizes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sizes_id_seq OWNED BY public.sizes.id;


--
-- Name: slides; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.slides (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    text text,
    tagline character varying(255),
    button_text character varying(60),
    button_link character varying(255),
    product_id bigint,
    main_image character varying(255),
    secondary_image character varying(255),
    thumb_image character varying(255),
    bg_color character varying(7) DEFAULT '#2b211c'::character varying NOT NULL,
    accent_color character varying(7) DEFAULT '#ff7a2f'::character varying NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: slides_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.slides_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: slides_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.slides_id_seq OWNED BY public.slides.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    phone character varying(40),
    document character varying(30),
    city character varying(80),
    address character varying(255),
    is_admin boolean DEFAULT false NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: colors id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.colors ALTER COLUMN id SET DEFAULT nextval('public.colors_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- Name: product_images id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_images ALTER COLUMN id SET DEFAULT nextval('public.product_images_id_seq'::regclass);


--
-- Name: product_variants id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants ALTER COLUMN id SET DEFAULT nextval('public.product_variants_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: sizes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sizes ALTER COLUMN id SET DEFAULT nextval('public.sizes_id_seq'::regclass);


--
-- Name: slides id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slides ALTER COLUMN id SET DEFAULT nextval('public.slides_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.categories (id, name, slug, description, image, sort_order, is_active, created_at, updated_at) VALUES (1, 'Remeras', 'remeras', 'Básicas y estampadas, de algodón.', NULL, 0, true, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.categories (id, name, slug, description, image, sort_order, is_active, created_at, updated_at) VALUES (2, 'Buzos', 'buzos', 'Canguros y cuello redondo de frisa.', NULL, 1, true, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.categories (id, name, slug, description, image, sort_order, is_active, created_at, updated_at) VALUES (3, 'Camperas', 'camperas', 'Inflables, livianas y abrigadas.', NULL, 2, true, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.categories (id, name, slug, description, image, sort_order, is_active, created_at, updated_at) VALUES (4, 'Pantalones', 'pantalones', 'Joggers y shorts.', NULL, 3, true, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.categories (id, name, slug, description, image, sort_order, is_active, created_at, updated_at) VALUES (5, 'Accesorios', 'accesorios', 'Gorras y bolsos.', NULL, 4, true, '2026-10-20 09:56:12', '2026-10-20 09:56:12');


--
-- Data for Name: colors; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (1, 'Blanco', '#f3f1ec', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (2, 'Negro', '#1d1c1b', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (3, 'Gris melange', '#9a9a98', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (4, 'Arena', '#d8c6a5', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (5, 'Verde salvia', '#9db09a', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (6, 'Oliva', '#5f6340', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (7, 'Azul marino', '#1f2a44', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (8, 'Celeste', '#a9c9e6', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (9, 'Rosa empolvado', '#e8b9b5', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (10, 'Lila', '#b7a3d6', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (11, 'Naranja', '#f26a21', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (12, 'Crema', '#ece3cf', 11, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (13, 'Azul cobalto', '#2846c4', 12, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (14, 'Verde bosque', '#244233', 13, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (15, 'Chocolate', '#4a3022', 14, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (16, 'Plateado', '#c9ccd1', 15, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (17, 'Cereza', '#a51e2c', 16, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.colors (id, name, hex, sort_order, created_at, updated_at) VALUES (18, 'Natural', '#e9dfc8', 17, '2026-10-20 09:56:12', '2026-10-20 09:56:12');


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: favorites; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.migrations (id, migration, batch) VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (4, '2026_10_20_000001_create_catalog_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (5, '2026_10_20_000002_create_slides_and_settings_tables', 1);
INSERT INTO public.migrations (id, migration, batch) VALUES (6, '2026_10_20_000003_create_orders_tables', 1);


--
-- Data for Name: order_items; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: product_images; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (1, 1, 1, 'seed/products/remera-terere-club--blanco.webp', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (2, 1, 1, 'seed/products/remera-terere-club--blanco--detalle.webp', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (3, 1, 5, 'seed/products/remera-terere-club--verde-salvia.webp', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (4, 1, 5, 'seed/products/remera-terere-club--verde-salvia--detalle.webp', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (5, 1, 2, 'seed/products/remera-terere-club--negro.webp', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (6, 1, 2, 'seed/products/remera-terere-club--negro--detalle.webp', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (7, 2, 2, 'seed/products/remera-mbarete-oversize--negro.webp', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (8, 2, 2, 'seed/products/remera-mbarete-oversize--negro--detalle.webp', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (9, 2, 4, 'seed/products/remera-mbarete-oversize--arena.webp', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (10, 2, 4, 'seed/products/remera-mbarete-oversize--arena--detalle.webp', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (11, 2, 17, 'seed/products/remera-mbarete-oversize--cereza.webp', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (12, 2, 17, 'seed/products/remera-mbarete-oversize--cereza--detalle.webp', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (13, 3, 1, 'seed/products/remera-nanduti--blanco.webp', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (14, 3, 1, 'seed/products/remera-nanduti--blanco--detalle.webp', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (15, 3, 9, 'seed/products/remera-nanduti--rosa.webp', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (16, 3, 9, 'seed/products/remera-nanduti--rosa--detalle.webp', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (17, 3, 8, 'seed/products/remera-nanduti--celeste.webp', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (18, 3, 8, 'seed/products/remera-nanduti--celeste--detalle.webp', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (19, 4, 8, 'seed/products/remera-asu-city--celeste.webp', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (20, 4, 8, 'seed/products/remera-asu-city--celeste--detalle.webp', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (21, 4, 1, 'seed/products/remera-asu-city--blanco.webp', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (22, 4, 1, 'seed/products/remera-asu-city--blanco--detalle.webp', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (23, 4, 7, 'seed/products/remera-asu-city--azul-marino.webp', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (24, 4, 7, 'seed/products/remera-asu-city--azul-marino--detalle.webp', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (25, 5, 1, 'seed/products/remera-basica-algodon--blanco.webp', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (26, 5, 1, 'seed/products/remera-basica-algodon--blanco--detalle.webp', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (27, 5, 2, 'seed/products/remera-basica-algodon--negro.webp', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (28, 5, 2, 'seed/products/remera-basica-algodon--negro--detalle.webp', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (29, 5, 3, 'seed/products/remera-basica-algodon--gris-melange.webp', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (30, 5, 3, 'seed/products/remera-basica-algodon--gris-melange--detalle.webp', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (31, 5, 7, 'seed/products/remera-basica-algodon--azul-marino.webp', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (32, 5, 7, 'seed/products/remera-basica-algodon--azul-marino--detalle.webp', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (33, 5, 6, 'seed/products/remera-basica-algodon--oliva.webp', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (34, 5, 6, 'seed/products/remera-basica-algodon--oliva--detalle.webp', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (35, 6, 10, 'seed/products/buzo-canguro-jopara--lila.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (36, 6, 10, 'seed/products/buzo-canguro-jopara--lila--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (37, 6, 3, 'seed/products/buzo-canguro-jopara--gris-melange.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (38, 6, 3, 'seed/products/buzo-canguro-jopara--gris-melange--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (39, 6, 2, 'seed/products/buzo-canguro-jopara--negro.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (40, 6, 2, 'seed/products/buzo-canguro-jopara--negro--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (41, 7, 6, 'seed/products/buzo-canguro-basico--oliva.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (42, 7, 6, 'seed/products/buzo-canguro-basico--oliva--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (43, 7, 11, 'seed/products/buzo-canguro-basico--naranja.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (44, 7, 11, 'seed/products/buzo-canguro-basico--naranja--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (45, 7, 12, 'seed/products/buzo-canguro-basico--crema.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (46, 7, 12, 'seed/products/buzo-canguro-basico--crema--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (47, 8, 13, 'seed/products/buzo-pora--cobalto.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (48, 8, 13, 'seed/products/buzo-pora--cobalto--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (49, 8, 3, 'seed/products/buzo-pora--gris-melange.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (50, 8, 3, 'seed/products/buzo-pora--gris-melange--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (51, 8, 12, 'seed/products/buzo-pora--crema.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (52, 8, 12, 'seed/products/buzo-pora--crema--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (53, 9, 11, 'seed/products/campera-inflable-chaco--naranja.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (54, 9, 11, 'seed/products/campera-inflable-chaco--naranja--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (55, 9, 2, 'seed/products/campera-inflable-chaco--negro.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (56, 9, 2, 'seed/products/campera-inflable-chaco--negro--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (57, 9, 14, 'seed/products/campera-inflable-chaco--verde-bosque.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (58, 9, 14, 'seed/products/campera-inflable-chaco--verde-bosque--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (59, 10, 16, 'seed/products/campera-inflable-corta--plateado.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (60, 10, 16, 'seed/products/campera-inflable-corta--plateado--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (61, 10, 15, 'seed/products/campera-inflable-corta--chocolate.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (62, 10, 15, 'seed/products/campera-inflable-corta--chocolate--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (63, 10, 10, 'seed/products/campera-inflable-corta--lila.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (64, 10, 10, 'seed/products/campera-inflable-corta--lila--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (65, 11, 2, 'seed/products/gorra-terere-club--negro.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (66, 11, 2, 'seed/products/gorra-terere-club--negro--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (67, 11, 12, 'seed/products/gorra-terere-club--crema.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (68, 11, 12, 'seed/products/gorra-terere-club--crema--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (69, 11, 14, 'seed/products/gorra-terere-club--verde-bosque.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (70, 11, 14, 'seed/products/gorra-terere-club--verde-bosque--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (71, 12, 13, 'seed/products/gorra-dad-hat--cobalto.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (72, 12, 13, 'seed/products/gorra-dad-hat--cobalto--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (73, 12, 4, 'seed/products/gorra-dad-hat--arena.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (74, 12, 4, 'seed/products/gorra-dad-hat--arena--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (75, 12, 17, 'seed/products/gorra-dad-hat--cereza.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (76, 12, 17, 'seed/products/gorra-dad-hat--cereza--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (77, 13, 2, 'seed/products/jogger-urbano--negro.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (78, 13, 2, 'seed/products/jogger-urbano--negro--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (79, 13, 3, 'seed/products/jogger-urbano--gris-melange.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (80, 13, 3, 'seed/products/jogger-urbano--gris-melange--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (81, 13, 6, 'seed/products/jogger-urbano--oliva.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (82, 13, 6, 'seed/products/jogger-urbano--oliva--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (83, 14, 2, 'seed/products/short-deportivo--negro.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (84, 14, 2, 'seed/products/short-deportivo--negro--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (85, 14, 7, 'seed/products/short-deportivo--azul-marino.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (86, 14, 7, 'seed/products/short-deportivo--azul-marino--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (87, 14, 5, 'seed/products/short-deportivo--verde-salvia.webp', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (88, 14, 5, 'seed/products/short-deportivo--verde-salvia--detalle.webp', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (89, 15, 18, 'seed/products/tote-nanduti--natural.webp', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (90, 15, 18, 'seed/products/tote-nanduti--natural--detalle.webp', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (91, 15, 2, 'seed/products/tote-nanduti--negro.webp', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_images (id, product_id, color_id, path, sort_order, created_at, updated_at) VALUES (92, 15, 2, 'seed/products/tote-nanduti--negro--detalle.webp', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');


--
-- Data for Name: product_variants; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (1, 1, 1, 1, 'TS-1-S-BLA', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (2, 1, 2, 1, 'TS-1-M-BLA', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (3, 1, 3, 1, 'TS-1-L-BLA', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (4, 1, 4, 1, 'TS-1-XL-BLA', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (5, 1, 5, 1, 'TS-1-XXL-BLA', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (6, 1, 1, 5, 'TS-1-S-VER', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (7, 1, 2, 5, 'TS-1-M-VER', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (8, 1, 3, 5, 'TS-1-L-VER', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (9, 1, 4, 5, 'TS-1-XL-VER', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (10, 1, 5, 5, 'TS-1-XXL-VER', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (11, 1, 1, 2, 'TS-1-S-NEG', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (12, 1, 2, 2, 'TS-1-M-NEG', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (13, 1, 3, 2, 'TS-1-L-NEG', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (14, 1, 4, 2, 'TS-1-XL-NEG', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (15, 1, 5, 2, 'TS-1-XXL-NEG', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (16, 2, 1, 2, 'TS-2-S-NEG', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (17, 2, 2, 2, 'TS-2-M-NEG', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (18, 2, 3, 2, 'TS-2-L-NEG', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (19, 2, 4, 2, 'TS-2-XL-NEG', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (20, 2, 5, 2, 'TS-2-XXL-NEG', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (21, 2, 1, 4, 'TS-2-S-ARE', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (22, 2, 2, 4, 'TS-2-M-ARE', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (23, 2, 3, 4, 'TS-2-L-ARE', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (24, 2, 4, 4, 'TS-2-XL-ARE', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (25, 2, 5, 4, 'TS-2-XXL-ARE', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (26, 2, 1, 17, 'TS-2-S-CER', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (27, 2, 2, 17, 'TS-2-M-CER', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (28, 2, 3, 17, 'TS-2-L-CER', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (29, 2, 4, 17, 'TS-2-XL-CER', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (30, 2, 5, 17, 'TS-2-XXL-CER', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (31, 3, 1, 1, 'TS-3-S-BLA', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (32, 3, 2, 1, 'TS-3-M-BLA', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (33, 3, 3, 1, 'TS-3-L-BLA', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (34, 3, 4, 1, 'TS-3-XL-BLA', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (35, 3, 5, 1, 'TS-3-XXL-BLA', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (36, 3, 1, 9, 'TS-3-S-ROS', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (37, 3, 2, 9, 'TS-3-M-ROS', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (38, 3, 3, 9, 'TS-3-L-ROS', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (39, 3, 4, 9, 'TS-3-XL-ROS', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (40, 3, 5, 9, 'TS-3-XXL-ROS', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (41, 3, 1, 8, 'TS-3-S-CEL', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (42, 3, 2, 8, 'TS-3-M-CEL', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (43, 3, 3, 8, 'TS-3-L-CEL', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (44, 3, 4, 8, 'TS-3-XL-CEL', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (45, 3, 5, 8, 'TS-3-XXL-CEL', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (46, 4, 1, 8, 'TS-4-S-CEL', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (47, 4, 2, 8, 'TS-4-M-CEL', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (48, 4, 3, 8, 'TS-4-L-CEL', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (49, 4, 4, 8, 'TS-4-XL-CEL', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (50, 4, 5, 8, 'TS-4-XXL-CEL', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (51, 4, 1, 1, 'TS-4-S-BLA', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (52, 4, 2, 1, 'TS-4-M-BLA', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (53, 4, 3, 1, 'TS-4-L-BLA', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (54, 4, 4, 1, 'TS-4-XL-BLA', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (55, 4, 5, 1, 'TS-4-XXL-BLA', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (56, 4, 1, 7, 'TS-4-S-AZU', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (57, 4, 2, 7, 'TS-4-M-AZU', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (58, 4, 3, 7, 'TS-4-L-AZU', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (59, 4, 4, 7, 'TS-4-XL-AZU', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (60, 4, 5, 7, 'TS-4-XXL-AZU', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (61, 5, 1, 1, 'TS-5-S-BLA', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (62, 5, 2, 1, 'TS-5-M-BLA', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (63, 5, 3, 1, 'TS-5-L-BLA', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (64, 5, 4, 1, 'TS-5-XL-BLA', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (65, 5, 5, 1, 'TS-5-XXL-BLA', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (66, 5, 1, 2, 'TS-5-S-NEG', 9, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (67, 5, 2, 2, 'TS-5-M-NEG', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (68, 5, 3, 2, 'TS-5-L-NEG', 8, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (69, 5, 4, 2, 'TS-5-XL-NEG', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (70, 5, 5, 2, 'TS-5-XXL-NEG', 7, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (71, 5, 1, 3, 'TS-5-S-GRI', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (72, 5, 2, 3, 'TS-5-M-GRI', 6, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (73, 5, 3, 3, 'TS-5-L-GRI', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (74, 5, 4, 3, 'TS-5-XL-GRI', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (75, 5, 5, 3, 'TS-5-XXL-GRI', 10, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (76, 5, 1, 7, 'TS-5-S-AZU', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (77, 5, 2, 7, 'TS-5-M-AZU', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (78, 5, 3, 7, 'TS-5-L-AZU', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (79, 5, 4, 7, 'TS-5-XL-AZU', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (80, 5, 5, 7, 'TS-5-XXL-AZU', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (81, 5, 1, 6, 'TS-5-S-OLI', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (82, 5, 2, 6, 'TS-5-M-OLI', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (83, 5, 3, 6, 'TS-5-L-OLI', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (84, 5, 4, 6, 'TS-5-XL-OLI', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (85, 5, 5, 6, 'TS-5-XXL-OLI', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (86, 6, 1, 10, 'TS-6-S-LIL', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (87, 6, 2, 10, 'TS-6-M-LIL', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (88, 6, 3, 10, 'TS-6-L-LIL', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (89, 6, 4, 10, 'TS-6-XL-LIL', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (90, 6, 5, 10, 'TS-6-XXL-LIL', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (91, 6, 1, 3, 'TS-6-S-GRI', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (92, 6, 2, 3, 'TS-6-M-GRI', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (93, 6, 3, 3, 'TS-6-L-GRI', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (94, 6, 4, 3, 'TS-6-XL-GRI', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (95, 6, 5, 3, 'TS-6-XXL-GRI', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (96, 6, 1, 2, 'TS-6-S-NEG', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (97, 6, 2, 2, 'TS-6-M-NEG', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (98, 6, 3, 2, 'TS-6-L-NEG', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (99, 6, 4, 2, 'TS-6-XL-NEG', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (100, 6, 5, 2, 'TS-6-XXL-NEG', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (101, 7, 1, 6, 'TS-7-S-OLI', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (102, 7, 2, 6, 'TS-7-M-OLI', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (103, 7, 3, 6, 'TS-7-L-OLI', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (104, 7, 4, 6, 'TS-7-XL-OLI', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (105, 7, 5, 6, 'TS-7-XXL-OLI', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (106, 7, 1, 11, 'TS-7-S-NAR', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (107, 7, 2, 11, 'TS-7-M-NAR', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (108, 7, 3, 11, 'TS-7-L-NAR', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (109, 7, 4, 11, 'TS-7-XL-NAR', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (110, 7, 5, 11, 'TS-7-XXL-NAR', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (111, 7, 1, 12, 'TS-7-S-CRE', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (112, 7, 2, 12, 'TS-7-M-CRE', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (113, 7, 3, 12, 'TS-7-L-CRE', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (114, 7, 4, 12, 'TS-7-XL-CRE', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (115, 7, 5, 12, 'TS-7-XXL-CRE', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (116, 8, 1, 13, 'TS-8-S-COB', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (117, 8, 2, 13, 'TS-8-M-COB', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (118, 8, 3, 13, 'TS-8-L-COB', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (119, 8, 4, 13, 'TS-8-XL-COB', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (120, 8, 5, 13, 'TS-8-XXL-COB', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (121, 8, 1, 3, 'TS-8-S-GRI', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (122, 8, 2, 3, 'TS-8-M-GRI', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (123, 8, 3, 3, 'TS-8-L-GRI', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (124, 8, 4, 3, 'TS-8-XL-GRI', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (125, 8, 5, 3, 'TS-8-XXL-GRI', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (126, 8, 1, 12, 'TS-8-S-CRE', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (127, 8, 2, 12, 'TS-8-M-CRE', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (128, 8, 3, 12, 'TS-8-L-CRE', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (129, 8, 4, 12, 'TS-8-XL-CRE', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (130, 8, 5, 12, 'TS-8-XXL-CRE', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (131, 9, 1, 11, 'TS-9-S-NAR', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (132, 9, 2, 11, 'TS-9-M-NAR', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (133, 9, 3, 11, 'TS-9-L-NAR', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (134, 9, 4, 11, 'TS-9-XL-NAR', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (135, 9, 5, 11, 'TS-9-XXL-NAR', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (136, 9, 1, 2, 'TS-9-S-NEG', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (137, 9, 2, 2, 'TS-9-M-NEG', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (138, 9, 3, 2, 'TS-9-L-NEG', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (139, 9, 4, 2, 'TS-9-XL-NEG', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (140, 9, 5, 2, 'TS-9-XXL-NEG', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (141, 9, 1, 14, 'TS-9-S-VER', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (142, 9, 2, 14, 'TS-9-M-VER', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (143, 9, 3, 14, 'TS-9-L-VER', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (144, 9, 4, 14, 'TS-9-XL-VER', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (145, 9, 5, 14, 'TS-9-XXL-VER', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (146, 10, 1, 16, 'TS-10-S-PLA', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (147, 10, 2, 16, 'TS-10-M-PLA', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (148, 10, 3, 16, 'TS-10-L-PLA', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (149, 10, 4, 16, 'TS-10-XL-PLA', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (150, 10, 5, 16, 'TS-10-XXL-PLA', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (151, 10, 1, 15, 'TS-10-S-CHO', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (152, 10, 2, 15, 'TS-10-M-CHO', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (153, 10, 3, 15, 'TS-10-L-CHO', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (154, 10, 4, 15, 'TS-10-XL-CHO', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (155, 10, 5, 15, 'TS-10-XXL-CHO', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (156, 10, 1, 10, 'TS-10-S-LIL', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (157, 10, 2, 10, 'TS-10-M-LIL', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (158, 10, 3, 10, 'TS-10-L-LIL', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (159, 10, 4, 10, 'TS-10-XL-LIL', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (160, 10, 5, 10, 'TS-10-XXL-LIL', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (161, 11, 6, 2, 'TS-11-Único-NEG', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (162, 11, 6, 12, 'TS-11-Único-CRE', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (163, 11, 6, 14, 'TS-11-Único-VER', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (164, 12, 6, 13, 'TS-12-Único-COB', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (165, 12, 6, 4, 'TS-12-Único-ARE', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (166, 12, 6, 17, 'TS-12-Único-CER', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (167, 13, 1, 2, 'TS-13-S-NEG', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (168, 13, 2, 2, 'TS-13-M-NEG', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (169, 13, 3, 2, 'TS-13-L-NEG', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (170, 13, 4, 2, 'TS-13-XL-NEG', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (171, 13, 1, 3, 'TS-13-S-GRI', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (172, 13, 2, 3, 'TS-13-M-GRI', 4, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (173, 13, 3, 3, 'TS-13-L-GRI', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (174, 13, 4, 3, 'TS-13-XL-GRI', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (175, 13, 1, 6, 'TS-13-S-OLI', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (176, 13, 2, 6, 'TS-13-M-OLI', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (177, 13, 3, 6, 'TS-13-L-OLI', 1, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (178, 13, 4, 6, 'TS-13-XL-OLI', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (179, 14, 1, 2, 'TS-14-S-NEG', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (180, 14, 2, 2, 'TS-14-M-NEG', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (181, 14, 3, 2, 'TS-14-L-NEG', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (182, 14, 4, 2, 'TS-14-XL-NEG', 7, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (183, 14, 1, 7, 'TS-14-S-AZU', 6, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (184, 14, 2, 7, 'TS-14-M-AZU', 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (185, 14, 3, 7, 'TS-14-L-AZU', 5, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (186, 14, 4, 7, 'TS-14-XL-AZU', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (187, 14, 1, 5, 'TS-14-S-VER', 9, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (188, 14, 2, 5, 'TS-14-M-VER', 3, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (189, 14, 3, 5, 'TS-14-L-VER', 8, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (190, 14, 4, 5, 'TS-14-XL-VER', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (191, 15, 6, 18, 'TS-15-Único-NAT', 10, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.product_variants (id, product_id, size_id, color_id, sku, stock, created_at, updated_at) VALUES (192, 15, 6, 2, 'TS-15-Único-NEG', 2, '2026-10-20 09:56:13', '2026-10-20 09:56:13');


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (1, 1, 'Remera Tereré Club', 'remera-terere-club', 'Remera de algodón peinado 24/1 con estampa al agua «Tereré Club». Calce regular, cuello ribeteado y costuras reforzadas. Para el que no larga la guampa ni en invierno.', 'TS-001', 139000, NULL, true, true, true, 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (2, 1, 'Remera Mbarete oversize', 'remera-mbarete-oversize', 'Corte oversize con hombro caído y tela pesada de 220 g. Estampa «Mbarete» en serigrafía. Se usa suelta, con jean o jogger.', 'TS-002', 159000, 189000, true, true, false, 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (3, 1, 'Remera Ñandutí', 'remera-nanduti', 'Homenaje al encaje de Itauguá: el ñandutí en una estampa grande al frente. Algodón suave, calce regular.', 'TS-003', 149000, NULL, true, true, true, 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (4, 1, 'Remera Asu City', 'remera-asu-city', 'Asunción desde 1537. Remera de algodón con estampa tipográfica y calce regular.', 'TS-004', 139000, NULL, true, false, false, 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (5, 1, 'Remera básica de algodón', 'remera-basica-algodon', 'La que se usa todos los días. Algodón peinado, cuello que no se deforma y cinco colores para combinar con todo.', 'TS-005', 89000, NULL, true, true, false, 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (6, 2, 'Buzo canguro Jopará', 'buzo-canguro-jopara', 'Buzo con capucha forrada, bolsillo canguro y puños elastizados. Frisa de algodón para las mañanas frescas.', 'TS-006', 289000, 329000, true, true, true, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (7, 2, 'Buzo canguro básico', 'buzo-canguro-basico', 'Liso, cómodo y de frisa pesada. Capucha con cordón y bolsillo canguro.', 'TS-007', 249000, NULL, true, false, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (8, 2, 'Buzo cuello redondo Porã', 'buzo-pora', '«Porã» es lindo en guaraní. Buzo de cuello redondo, frisa suave y puños con elástico.', 'TS-008', 259000, NULL, true, false, true, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (9, 3, 'Campera inflable Chaco', 'campera-inflable-chaco', 'Liviana, abrigada y con mucha onda. Relleno sintético, cuello alto y cierre frontal. Se guarda en su propia bolsa.', 'TS-009', 459000, 599000, true, true, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (10, 3, 'Campera inflable corta', 'campera-inflable-corta', 'Versión corta para combinar con tiro alto. Brillo suave, cuello alto y puños elastizados.', 'TS-010', 419000, NULL, true, false, true, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (11, 5, 'Gorra Tereré Club', 'gorra-terere-club', 'Gorra de seis paneles con bordado al frente y regulador trasero.', 'TS-011', 99000, NULL, true, true, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (12, 5, 'Gorra dad hat t_shirt', 'gorra-dad-hat', 'Visera curva, copa baja y logo bordado. Algodón lavado.', 'TS-012', 95000, NULL, true, false, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (13, 4, 'Jogger urbano', 'jogger-urbano', 'Jogger de frisa con cintura elástica, cordón y puños. Bolsillos laterales profundos.', 'TS-013', 219000, NULL, true, true, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (14, 4, 'Short deportivo', 'short-deportivo', 'Short de algodón liviano con cintura elástica. Para el calor de diciembre y de casi todo el año.', 'TS-014', 149000, NULL, true, false, false, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.products (id, category_id, name, slug, description, sku, price, compare_price, is_active, is_featured, is_new, sold_count, created_at, updated_at) VALUES (15, 5, 'Bolso tote Ñandutí', 'tote-nanduti', 'Bolso de lona gruesa con estampa ñandutí. Entra la notebook, el termo y todo lo demás.', 'TS-015', 79000, NULL, true, false, true, 0, '2026-10-20 09:56:13', '2026-10-20 09:56:13');


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('store_name', 't_shirtparaguay', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('store_tagline', 'Ropa urbana hecha para el calor de acá', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('currency', 'Gs.', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('whatsapp', '595981000000', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('instagram', 'https://instagram.com/tshirtparaguay', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('facebook', '', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('tiktok', '', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('email', 'hola@tshirtparaguay.com', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('address', 'Asunción, Paraguay', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('pickup_address', 'Av. Mcal. López 1234, Asunción (lunes a sábado de 9 a 19 h)', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('shipping_cost', '25000', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('free_shipping_from', '400000', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('low_stock', '3', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('bank_details', 'Banco: Itaú
Cuenta: 123456789
Titular: t_shirtparaguay
RUC: 80000000-0
Alias: tshirtpy', '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.settings (key, value, created_at, updated_at) VALUES ('announcement', 'Envío gratis en Asunción y Central desde Gs. 400.000', '2026-10-20 09:56:12', '2026-10-20 09:56:12');


--
-- Data for Name: sizes; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (1, 'S', 0, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (2, 'M', 1, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (3, 'L', 2, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (4, 'XL', 3, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (5, 'XXL', 4, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.sizes (id, name, sort_order, created_at, updated_at) VALUES (6, 'Único', 5, '2026-10-20 09:56:12', '2026-10-20 09:56:12');


--
-- Data for Name: slides; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.slides (id, title, text, tagline, button_text, button_link, product_id, main_image, secondary_image, thumb_image, bg_color, accent_color, sort_order, is_active, created_at, updated_at) VALUES (1, 'Abrigo que se nota', 'Liviana, abrigada y con cuello alto. La campera inflable Chaco te acompaña de la facultad a la previa sin pesar nada.', 'Calor, con estilo.', 'Ver la campera', NULL, 9, 'seed/cut/campera-inflable-chaco--naranja.webp', 'seed/cut/campera-inflable-chaco--negro.webp', 'seed/cut/campera-inflable-corta--plateado.webp', '#3a2418', '#ff7a2f', 0, true, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.slides (id, title, text, tagline, button_text, button_link, product_id, main_image, secondary_image, thumb_image, bg_color, accent_color, sort_order, is_active, created_at, updated_at) VALUES (2, 'Jopará de estilos', 'Buzo canguro de frisa con capucha forrada. Mezclalo con lo que tengas: queda bien con todo.', 'Suave por dentro, mbarete por fuera.', 'Ver el buzo', NULL, 6, 'seed/cut/buzo-canguro-jopara--lila.webp', 'seed/cut/buzo-canguro-jopara--negro.webp', 'seed/cut/buzo-pora--cobalto.webp', '#2d2440', '#c8b4ff', 1, true, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.slides (id, title, text, tagline, button_text, button_link, product_id, main_image, secondary_image, thumb_image, bg_color, accent_color, sort_order, is_active, created_at, updated_at) VALUES (3, 'Tereré Club', 'La remera para la ronda de la tarde. Algodón peinado, estampa al agua y tres colores.', 'Hecho para el calor de acá.', 'Ver la remera', NULL, 1, 'seed/cut/remera-terere-club--verde-salvia.webp', 'seed/cut/remera-terere-club--blanco.webp', 'seed/cut/gorra-terere-club--negro.webp', '#1f3326', '#b9e4a8', 2, true, '2026-10-20 09:56:13', '2026-10-20 09:56:13');
INSERT INTO public.slides (id, title, text, tagline, button_text, button_link, product_id, main_image, secondary_image, thumb_image, bg_color, accent_color, sort_order, is_active, created_at, updated_at) VALUES (4, 'Gorras con onda', 'Seis paneles, bordado al frente y regulador. Completá el look por menos de cien mil.', 'El detalle que faltaba.', 'Ver gorras', NULL, 12, 'seed/cut/gorra-dad-hat--cobalto.webp', 'seed/cut/gorra-terere-club--crema.webp', 'seed/cut/tote-nanduti--natural.webp', '#14215e', '#9fb4ff', 3, true, '2026-10-20 09:56:13', '2026-10-20 09:56:13');


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.users (id, name, email, email_verified_at, password, phone, document, city, address, is_admin, remember_token, created_at, updated_at) VALUES (1, 'Administrador', 'admin@tshirtparaguay.com', NULL, '$2y$12$012uOjwxz5HKyrD6oRomBOvvkRmvLwlOttfvbjTs1.d/1nBQ02wBy', NULL, NULL, NULL, NULL, true, NULL, '2026-10-20 09:56:12', '2026-10-20 09:56:12');
INSERT INTO public.users (id, name, email, email_verified_at, password, phone, document, city, address, is_admin, remember_token, created_at, updated_at) VALUES (2, 'Lucía Benítez', 'cliente@ejemplo.com', NULL, '$2y$12$2QyUhHpJAxrq//MUJSx60.mDz/401FFmMaRInZXXm8L/FTI8GKVPW', '0981 123 456', NULL, 'Asunción', 'Av. España 1234', false, NULL, '2026-10-20 09:56:12', '2026-10-20 09:56:12');


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories_id_seq', 5, true);


--
-- Name: colors_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.colors_id_seq', 18, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 6, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.order_items_id_seq', 1, false);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orders_id_seq', 1, false);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- Name: product_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_images_id_seq', 92, true);


--
-- Name: product_variants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.product_variants_id_seq', 192, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.products_id_seq', 15, true);


--
-- Name: sizes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.sizes_id_seq', 6, true);


--
-- Name: slides_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.slides_id_seq', 4, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 2, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: categories categories_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_slug_unique UNIQUE (slug);


--
-- Name: colors colors_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_name_unique UNIQUE (name);


--
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: favorites favorites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_pkey PRIMARY KEY (user_id, product_id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_number_unique UNIQUE (number);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: product_images product_images_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_images
    ADD CONSTRAINT product_images_pkey PRIMARY KEY (id);


--
-- Name: product_variants product_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_pkey PRIMARY KEY (id);


--
-- Name: product_variants product_variants_product_id_size_id_color_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_product_id_size_id_color_id_unique UNIQUE (product_id, size_id, color_id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: products products_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_slug_unique UNIQUE (slug);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (key);


--
-- Name: sizes sizes_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sizes
    ADD CONSTRAINT sizes_name_unique UNIQUE (name);


--
-- Name: sizes sizes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sizes
    ADD CONSTRAINT sizes_pkey PRIMARY KEY (id);


--
-- Name: slides slides_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slides
    ADD CONSTRAINT slides_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: orders_status_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX orders_status_created_at_index ON public.orders USING btree (status, created_at);


--
-- Name: payments_gateway_transaction_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payments_gateway_transaction_id_index ON public.payments USING btree (gateway, transaction_id);


--
-- Name: products_is_active_category_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_is_active_category_id_index ON public.products USING btree (is_active, category_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: favorites favorites_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: favorites favorites_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.favorites
    ADD CONSTRAINT favorites_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE SET NULL;


--
-- Name: order_items order_items_product_variant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_variant_id_foreign FOREIGN KEY (product_variant_id) REFERENCES public.product_variants(id) ON DELETE SET NULL;


--
-- Name: orders orders_seller_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_seller_id_foreign FOREIGN KEY (seller_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: orders orders_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: payments payments_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: product_images product_images_color_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_images
    ADD CONSTRAINT product_images_color_id_foreign FOREIGN KEY (color_id) REFERENCES public.colors(id) ON DELETE SET NULL;


--
-- Name: product_images product_images_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_images
    ADD CONSTRAINT product_images_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_variants product_variants_color_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_color_id_foreign FOREIGN KEY (color_id) REFERENCES public.colors(id) ON DELETE RESTRICT;


--
-- Name: product_variants product_variants_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_variants product_variants_size_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_size_id_foreign FOREIGN KEY (size_id) REFERENCES public.sizes(id) ON DELETE RESTRICT;


--
-- Name: products products_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE SET NULL;


--
-- Name: slides slides_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.slides
    ADD CONSTRAINT slides_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--


