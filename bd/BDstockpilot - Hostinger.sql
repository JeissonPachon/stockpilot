-- BD StockPilot v4.6 (versión adaptada según indicaciones del usuario)
-- Basado en el script original. Fuente: archivo proporcionado por el usuario. :contentReference[oaicite:1]{index=1}

-- TABLAS --
USE u414216290_stockpilot;

CREATE TABLE usuario (
    idusu INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomusu VARCHAR(100),
    apeusu VARCHAR(100),
    tdousu VARCHAR(20) COMMENT 'Tipo doc (valor dominio)',
    ndousu VARCHAR(20),
    celusu VARCHAR(15),
    emausu VARCHAR(100),
    pasusu VARCHAR(255),
    imgusu VARCHAR(255),
    idper INT(10),
    keyolv VARCHAR(255),
    fecsol DATETIME,
    bloqkey TINYINT(1),
    ultlogin DATETIME,
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE usuario_empresa (
    idusu INT(10),
    idemp INT(10),
    fec_crea DATETIME
);

CREATE TABLE perfil (
    idper INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomper VARCHAR(100),
    -- Permisos CRUD
    ver TINYINT(1),
    crear TINYINT(1),
    editar TINYINT(1),
    eliminar TINYINT(1),
    act TINYINT(1) DEFAULT 1
);


    CREATE TABLE empresa (
        estado TINYINT(1),
        idemp INT(10) PRIMARY KEY AUTO_INCREMENT,
        nomemp VARCHAR(100),
        razemp VARCHAR(150),
        nitemp VARCHAR(20) UNIQUE,
        diremp VARCHAR(150),
        telemp VARCHAR(15),
        emaemp VARCHAR(100),
        logo VARCHAR(255),
        idusu INT(10) COMMENT 'Usuario creador',
        fec_crea DATETIME,
        fec_actu DATETIME,
        act TINYINT(1)
    );

CREATE TABLE ubicacion (
    idubi INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomubi VARCHAR(100),
    codubi VARCHAR(20),
    dirubi VARCHAR(150),
    depubi VARCHAR(100),
    ciuubi VARCHAR(100),
    idemp INT(10),
    idresp INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE categoria (
    idcat INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomcat VARCHAR(100),
    descat VARCHAR(255),
    idemp INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE producto (
    tipo_inventario TINYINT(1) COMMENT '1=Mercancías, 2=Materia Prima, 3=En Proceso, 4=Terminados',
    idprod INT(10) PRIMARY KEY AUTO_INCREMENT,
    codprod VARCHAR(20),
    nomprod VARCHAR(100),
    desprod VARCHAR(200),
    idcat INT(10),
    idemp INT(10),
    unimed VARCHAR(20),
    stkmin INT,
    stkmax INT,
    imgprod VARCHAR(255),
    costouni DECIMAL(12,2) COMMENT 'Costo unitario (opcional)',
    precioven DECIMAL(12,2) COMMENT 'Precio de venta (opcional)',
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);


CREATE TABLE proveedor (
    idprov INT(10) PRIMARY KEY AUTO_INCREMENT,
    idubi INT(10),
    tipoprov VARCHAR(20),
    nomprov VARCHAR(100),
    docprov VARCHAR(20),
    telprov VARCHAR(15),
    emaprov VARCHAR(100),
    dirprov VARCHAR(150),
    idemp INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE inventario (
    idinv INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idprod INT(10),
    idubi INT(10),
    cant INT,
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE kardex (
    idkar INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    anio INT,
    mes TINYINT,
    cerrado TINYINT(1),
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE movim (
    idmov INT(10) PRIMARY KEY AUTO_INCREMENT,
    idkar INT(10),
    idprod INT(10),
    idubi INT(10),
    fecmov DATE,
    tipmov TINYINT(2) COMMENT '1=ENTRADA, 2=SALIDA',
    cantmov INT,
    valmov DECIMAL(12,2),
    costprom DECIMAL(12,2),
    docref VARCHAR(50),
    obs TEXT,
    idusu INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE solentrada (
    idsol INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idprov INT(10),
    idubi INT(10),
    fecsol DATE,
    fecent DATE,
    tippag VARCHAR(20),
    estsol VARCHAR(20),
    totsol DECIMAL(12,2),
    obssol TEXT,
    idusu INT(10),
    idusu_apr INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE solsalida (
    idsol INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idubi INT(10),
    fecsol DATE,
    estsol VARCHAR(20),
    totsol DECIMAL(12,2),
    obssol TEXT,
    idusu INT(10),
    idusu_apr INT(10),
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE detentrada (
    iddet INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idsol INT(10),
    idprod INT(10),
    cantdet INT,
    vundet DECIMAL(10,2),
    totdet DECIMAL(10,2) GENERATED ALWAYS AS (cantdet * vundet) STORED,
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE detsalida (
    iddet INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idsol INT(10),
    idprod INT(10),
    cantdet INT,
    vundet DECIMAL(10,2),
    totdet DECIMAL(10,2) GENERATED ALWAYS AS (cantdet * vundet) STORED,
    fec_crea DATETIME,
    fec_actu DATETIME
);

CREATE TABLE dominio (
    iddom INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomdom VARCHAR(100),
    desdom VARCHAR(255),
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE valor (
    idval INT(10) PRIMARY KEY AUTO_INCREMENT,
    nomval VARCHAR(100),
    iddom INT(10),
    codval VARCHAR(20),
    desval VARCHAR(255),
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE modulo (
    idmod INT(10) PRIMARY KEY AUTO_INCREMENT,
    nommod VARCHAR(100),
    icono VARCHAR(50),
    ruta VARCHAR(100),
    orden TINYINT,
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE pagina (
    idpag INT(10) PRIMARY KEY AUTO_INCREMENT,
    idmod INT(10),
    nompag VARCHAR(100),
    ruta VARCHAR(100),
    icono VARCHAR(50),
    orden TINYINT,
    fec_crea DATETIME,
    fec_actu DATETIME,
    act TINYINT(1)
);

CREATE TABLE pxp (
    idper INT(10),
    idpag INT(10)
);

CREATE TABLE IF NOT EXISTS auditoria (
    idaud INT(10) PRIMARY KEY AUTO_INCREMENT,
    idemp INT(10),
    idusu INT(10),
    tabla VARCHAR(50),
    accion TINYINT(2) COMMENT'' ,
    idreg INT(10),
    datos_ant TEXT,
    datos_nue TEXT,
    email VARCHAR(100) COMMENT'' ,
    exitoso TINYINT(1) COMMENT '',
    navegador VARCHAR(255) COMMENT'' ,
    fecha DATETIME,
    ip VARCHAR(45)
);

CREATE TABLE lote (
    idlote INT(10) PRIMARY KEY AUTO_INCREMENT,
    idprod INT(10),          -- Producto asociado
    codlote VARCHAR(50),     -- Código o referencia del lote
    fecven DATE,             -- Fecha de vencimiento (opcional)
    cant INT,                -- Cantidad disponible en el lote
    fec_crea DATETIME,
    fec_actu DATETIME
);

-- INDICES --

-- Mantengo índices únicos de negocio (si quieres que los quite, lo hago)
ALTER TABLE inventario ADD UNIQUE KEY uk_inv_emp_prod_ubi (idemp, idprod, idubi);


-- Para seguir el estilo "instructor" añadimos índices en las tablas intermedias y FKs después
ALTER TABLE usuario_empresa ADD KEY fk_ue_idusu (idusu);
ALTER TABLE usuario_empresa ADD KEY fk_ue_idemp (idemp);

ALTER TABLE pagina ADD KEY fk_pg_idmod (idmod);
ALTER TABLE pxp ADD KEY fk_pxp_idper (idper);
ALTER TABLE pxp ADD KEY fk_pxp_idpag (idpag);

ALTER TABLE proveedor ADD KEY fk_prv_idubi (idubi);
ALTER TABLE proveedor ADD KEY fk_prv_idemp (idemp);



ALTER TABLE producto ADD KEY fk_prod_idcat (idcat);
ALTER TABLE producto ADD KEY fk_prod_idemp (idemp);

ALTER TABLE inventario ADD KEY fk_inv_idemp (idemp);
ALTER TABLE inventario ADD KEY fk_inv_idprod (idprod);
ALTER TABLE inventario ADD KEY fk_inv_idubi (idubi);



ALTER TABLE solentrada ADD KEY fk_solent_idemp (idemp);
ALTER TABLE solentrada ADD KEY fk_solent_idprov (idprov);
ALTER TABLE solentrada ADD KEY fk_solent_idubi (idubi);
ALTER TABLE solentrada ADD KEY fk_solent_idusu (idusu);
ALTER TABLE solentrada ADD KEY fk_solent_idusuapr (idusu_apr);

ALTER TABLE solsalida ADD KEY fk_solsal_idemp (idemp);
ALTER TABLE solsalida ADD KEY fk_solsal_idubi (idubi);
ALTER TABLE solsalida ADD KEY fk_solsal_idusu (idusu);
ALTER TABLE solsalida ADD KEY fk_solsal_idusuapr (idusu_apr);

ALTER TABLE detentrada ADD KEY fk_detent_idemp (idemp);
ALTER TABLE detentrada ADD KEY fk_detent_idsol (idsol);
ALTER TABLE detentrada ADD KEY fk_detent_idprod (idprod);

ALTER TABLE detsalida ADD KEY fk_detsal_idemp (idemp);
ALTER TABLE detsalida ADD KEY fk_detsal_idsol (idsol);
ALTER TABLE detsalida ADD KEY fk_detsal_idprod (idprod);

ALTER TABLE valor ADD KEY fk_val_iddom (iddom);

ALTER TABLE lote ADD KEY fk_lote_idprod (idprod);

-- RELACIONES --

ALTER TABLE usuario ADD CONSTRAINT fkuspe FOREIGN KEY (idper) REFERENCES perfil(idper);

ALTER TABLE usuario_empresa
  ADD CONSTRAINT fk_ue_us FOREIGN KEY (idusu) REFERENCES usuario(idusu),
  ADD CONSTRAINT fk_ue_em FOREIGN KEY (idemp) REFERENCES empresa(idemp);

ALTER TABLE empresa ADD CONSTRAINT fkemus FOREIGN KEY (idusu) REFERENCES usuario(idusu);



ALTER TABLE categoria ADD CONSTRAINT fkcaem FOREIGN KEY (idemp) REFERENCES empresa(idemp);

ALTER TABLE producto ADD CONSTRAINT fkprca FOREIGN KEY (idcat) REFERENCES categoria(idcat);
ALTER TABLE producto ADD CONSTRAINT fkprem FOREIGN KEY (idemp) REFERENCES empresa(idemp);

ALTER TABLE proveedor ADD CONSTRAINT fkprub FOREIGN KEY (idubi) REFERENCES ubicacion(idubi);
ALTER TABLE proveedor ADD CONSTRAINT fkpremp FOREIGN KEY (idemp) REFERENCES empresa(idemp);

ALTER TABLE inventario
  ADD CONSTRAINT fk_inv_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp),
  ADD CONSTRAINT fk_inv_prod FOREIGN KEY (idprod) REFERENCES producto(idprod),
  ADD CONSTRAINT fk_inv_ubi FOREIGN KEY (idubi) REFERENCES ubicacion(idubi);

ALTER TABLE kardex ADD CONSTRAINT fkkaem FOREIGN KEY (idemp) REFERENCES empresa(idemp);

ALTER TABLE movim
  ADD CONSTRAINT fk_movim_kar FOREIGN KEY (idkar) REFERENCES kardex(idkar),
  ADD CONSTRAINT fk_movim_prod FOREIGN KEY (idprod) REFERENCES producto(idprod),
  ADD CONSTRAINT fk_movim_ubi FOREIGN KEY (idubi) REFERENCES ubicacion(idubi),
  ADD CONSTRAINT fk_movim_usu FOREIGN KEY (idusu) REFERENCES usuario(idusu);

ALTER TABLE solentrada
  ADD CONSTRAINT fk_solent_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp),
  ADD CONSTRAINT fk_solent_prov FOREIGN KEY (idprov) REFERENCES proveedor(idprov),
  ADD CONSTRAINT fk_solent_ubi FOREIGN KEY (idubi) REFERENCES ubicacion(idubi),
  ADD CONSTRAINT fk_solent_usu FOREIGN KEY (idusu) REFERENCES usuario(idusu),
  ADD CONSTRAINT fk_solent_usuapr FOREIGN KEY (idusu_apr) REFERENCES usuario(idusu);

ALTER TABLE solsalida
  ADD CONSTRAINT fk_solsal_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp),
  ADD CONSTRAINT fk_solsal_ubi FOREIGN KEY (idubi) REFERENCES ubicacion(idubi),
  ADD CONSTRAINT fk_solsal_usu FOREIGN KEY (idusu) REFERENCES usuario(idusu),
  ADD CONSTRAINT fk_solsal_usuapr FOREIGN KEY (idusu_apr) REFERENCES usuario(idusu);

ALTER TABLE detentrada
  ADD CONSTRAINT fk_detent_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp),
  ADD CONSTRAINT fk_detent_ids FOREIGN KEY (idsol) REFERENCES solentrada(idsol) ON DELETE CASCADE,
  ADD CONSTRAINT fk_detent_prod FOREIGN KEY (idprod) REFERENCES producto(idprod);

ALTER TABLE detsalida
  ADD CONSTRAINT fk_detsal_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp),
  ADD CONSTRAINT fk_detsal_ids FOREIGN KEY (idsol) REFERENCES solsalida(idsol) ON DELETE CASCADE,
  ADD CONSTRAINT fk_detsal_prod FOREIGN KEY (idprod) REFERENCES producto(idprod);

ALTER TABLE valor ADD CONSTRAINT fkvldm FOREIGN KEY (iddom) REFERENCES dominio(iddom);
ALTER TABLE pagina ADD CONSTRAINT fkpgmo FOREIGN KEY (idmod) REFERENCES modulo(idmod);
ALTER TABLE pxp ADD CONSTRAINT fkpxpe FOREIGN KEY (idper) REFERENCES perfil(idper);
ALTER TABLE pxp ADD CONSTRAINT fkpxpg FOREIGN KEY (idpag) REFERENCES pagina(idpag);

ALTER TABLE auditoria ADD CONSTRAINT fkauem FOREIGN KEY (idemp) REFERENCES empresa(idemp);
ALTER TABLE auditoria ADD CONSTRAINT fkauus FOREIGN KEY (idusu) REFERENCES usuario(idusu);

ALTER TABLE lote ADD CONSTRAINT fk_lote_prod FOREIGN KEY (idprod) REFERENCES producto(idprod);


ALTER TABLE auditoria ADD KEY idx_aud_idemp (idemp);
ALTER TABLE auditoria ADD KEY idx_aud_idusu (idusu);
ALTER TABLE auditoria ADD KEY idx_aud_fecha (fecha);
ALTER TABLE auditoria ADD KEY idx_aud_tabla (tabla);


ALTER TABLE ubicacion ADD KEY idx_ubi_idemp (idemp);
ALTER TABLE ubicacion ADD KEY idx_ubi_idresp (idresp);


ALTER TABLE kardex ADD UNIQUE KEY uk_kardex (idemp, anio, mes);
ALTER TABLE kardex ADD KEY idx_kar_idemp (idemp);


ALTER TABLE movim ADD KEY idx_movim_idkar (idkar);
ALTER TABLE movim ADD KEY idx_movim_idprod (idprod);
ALTER TABLE movim ADD KEY idx_movim_idubi (idubi);
ALTER TABLE movim ADD KEY idx_movim_idusu (idusu);
ALTER TABLE movim ADD KEY idx_movim_fecmov (fecmov);



-- DATOS DE PRUEBA --

INSERT INTO `perfil` (`idper`, `nomper`, `ver`, `crear`, `editar`, `eliminar`, `act`) VALUES
(1, 'Superadmin', 1, 1, 1, 1, 1),
(2, 'Admin/empresa', 1, 1, 1, 0, 1),
(3, 'Empleado', 1, 0, 0, 0, 1);

INSERT INTO `dominio` (`iddom`, `nomdom`, `desdom`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 'tipo de documento', 'identificacion de personas', '2025-11-06 00:00:00', NULL, NULL);

INSERT INTO `usuario` (
    `idusu`, `nomusu`, `apeusu`, `tdousu`, `ndousu`, `celusu`, `emausu`, `pasusu`, `imgusu`, `idper`, `keyolv`, `fecsol`, `bloqkey`, `ultlogin`, `fec_crea`, `fec_actu`, `act`
) VALUES
(1, 'Admin', 'Sistema', 'CC', '123456789', '3001234567', 'admin@gmail.com', 'e0f53c0a8c931f995f898d5f166491ccbdc7f528kjahw9', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 'Juan', 'Pérez', 'CC', '987654321', '3102345678', 'juan@example.com', '5ccf87149ef220160cbadb2a47d34b4e9e54a96ckjahw9', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 'María', 'Gómez', 'TI', '1122334455', '3203456789', 'maria@example.com', '05fa67e8314738abfd59c29afb9eb6b2830085d1kjahw9', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(4, 'Pedro', 'Rodríguez', 'CC', '2233445566', '3009876543', 'pedro@example.com', '$2b$12$6oTkFgnxtIkSkZsTnjy5Zu3ydEEdgUUU9PA46z3DGljO3U2KPCclq', NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1), -- 💡 CAMBIO: idper de 1 a 2
(5, 'Laura', 'Martínez', 'CE', '3344556677', '3158765432', 'laura@example.com', '36627b1fd0666f45a836aaae46ee6e065e16bc0ekjahw9', NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 'juliana', 'Garcia zambrano', 'cc', '654654655454', '3115085565', 'juliszz@hotmail.com', '$2y$10$0/Zc/FGwz68sx8/o6AW3C.UfGq0uwkCyD09bGLT2KosnLEWiNjiwC', NULL, 2, NULL, NULL, NULL, NULL, '2025-11-02 08:56:50', '2025-11-02 08:56:50', 1),
(7, 'Pedro', 'Rodriguez', 'cc', '80501123', '3001548988', 'pedro54@gmail.com', '$2y$10$8.KOt3xA1G8HDeiQGQ3xB.aixOG/GiJEsARXe1JNbAZousKdpe4W2', NULL, 2, NULL, NULL, NULL, NULL, '2025-11-04 01:20:36', '2025-11-04 01:20:36', 1),
(9, 'Edward', 'Salinas', 'cc', '10024585899', '31154892459', 'Edw35689@gmail.com', '$2y$10$ClHAIl1xV2wW2WgL0dsNxOLXNvdalBZcoR6EEtbWIvRrM/VWheXoa', NULL, 3, NULL, NULL, NULL, NULL, '2025-11-19 23:31:26', '2025-11-19 23:31:26', 1),
(10, 'Felipillo', 'el pillo', 'cc', '1000548415', '32135654879', 'felipillo@gmail.com', '$2y$10$5Qoj10.btSCaLSbOqzyDVeW8GBPLdbS.X2oXWY35gdgoTZ5j32IBW', NULL, 3, NULL, NULL, NULL, NULL, '2025-11-20 20:13:03', '2025-11-20 20:13:03', 1),
(11, 'Daniel', 'perez', 'cc', '987654321', '3112223334', 'danipe@gmail.com', '376834d79e5ef2d0bb529c4b653c2685e2474fb0kjahw9', NULL, 3, NULL, NULL, NULL, NULL, '2025-11-20 20:53:45', '2025-11-20 20:53:45', 1),
(12, 'Asli', 'Gomez', 'cc', '1075657281', '3045968414', 'asliuwu@gmail.com', 'b032fe2e0347e76e40a48b7235c7dc736e3cc2dekjahw9', NULL, 3, NULL, NULL, NULL, NULL, '2025-11-20 20:55:51', '2025-11-20 20:55:51', 1),
(13, 'juan', 'garcia', 'cc', '1007694939', '3112381135', 'juangz3300@gmail.com', '01620e65123eab5de0498878d74ed1d85fa52286kjahw9', NULL, 3, NULL, NULL, NULL, NULL, '2025-11-21 07:08:29', '2025-11-21 07:08:29', 1);

INSERT INTO `empresa` (`estado`, `idemp`, `nomemp`, `razemp`, `nitemp`, `diremp`, `telemp`, `emaemp`, `logo`, `idusu`, `fec_crea`, `fec_actu`, `act`) VALUES
(NULL, 1, 'TechSolutions SA', 'TechSolutions Sociedad Anónima', '123456789-1', 'Calle 123 #45-67, Bogotá', '6012345678', 'contacto@techsolutions.com', NULL, 2, NULL, NULL, 1),  -- 💡 CAMBIO: De 1 (SA) a 2 (Admin)
(NULL, 2, 'DistriElectro', 'Distribuidora Electrónica Ltda', '987654321-1', 'Av. Principal 890, Medellín', '6045678901', 'ventas@distrielectro.com', NULL, 6, NULL, NULL, 1),  -- 💡 CAMBIO: De 1 (SA) a 6 (Admin)
(NULL, 3, 'Ferretería El Tornillo grande uwu', 'Ferretería El Tornillo SAS', '555444333-24', 'Cra. 45 #56-78, Cali', '6023456789', 'info@eltornillo.com', '', 7, NULL, NULL, 1),  -- 💡 CAMBIO: De NULL a 7 (Admin)
(NULL, 4, 'Papelería Moderna', 'Papelería Moderna Ltda', '111222333-4', 'Av. Comercial 123, Medellín', '6056789012', 'contacto@papeleriamoderna.com', NULL, 3, NULL, NULL, 1), -- Mantenido: 3 (Admin)
(NULL, 5, 'Bodega Central', 'Bodega Central SAS', '999888777-5', 'Zona Industrial, Barranquilla', '6051234567', 'bodega@central.com', NULL, 4, NULL, NULL, 1);  -- Mantenido: 4 (Admin)

INSERT INTO `categoria` (`idcat`, `nomcat`, `descat`, `idemp`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 'Electrónica', 'Dispositivos y componentes electrónicos', 1, NULL, NULL, NULL),
(2, 'Herramientas', 'Herramientas manuales y eléctricas', 1, NULL, NULL, NULL),
(3, 'Insumos', 'Materiales de oficina y limpieza', 2, NULL, NULL, NULL),
(4, 'Muebles', 'Mobiliario de oficina', 3, NULL, NULL, NULL),
(5, 'Repuestos', 'Repuestos industriales', 4, NULL, NULL, NULL);

INSERT INTO `ubicacion` (`idubi`, `nomubi`, `codubi`, `dirubi`, `depubi`, `ciuubi`, `idemp`, `idresp`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 'Bodega Principal', 'BOD-01', 'Calle 123 #45-67', 'Bogotá DC', 'Bogotá', 1, 1, NULL, NULL, NULL),
(2, 'Centro de Distribución', 'BOD-02', 'Av. Industrial 789', 'Antioquia', 'Medellín', 2, 1, NULL, NULL, NULL),
(3, 'Almacén Cali', 'BOD-03', 'Cra. 45 #56-78', 'Valle', 'Cali', 3, 2, NULL, NULL, NULL),
(4, 'Depósito Medellín', 'BOD-04', 'Av. Comercial 123', 'Antioquia', 'Medellín', 4, 3, NULL, NULL, NULL),
(5, 'Bodega Barranquilla', 'BOD-05', 'Zona Industrial', 'Atlántico', 'Barranquilla', 5, 4, NULL, NULL, NULL);

INSERT INTO `proveedor` (`idprov`, `idubi`, `tipoprov`, `nomprov`, `docprov`, `telprov`, `emaprov`, `dirprov`, `idemp`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 1, 'Jurídico', 'ElectroParts SA', '987654321-2', '6012345679', 'compras@electroparts.com', 'Calle 456 #78-90, Bogotá', 1, NULL, NULL, NULL),
(2, 2, 'Jurídico', 'Papelería Moderna', '543216789-1', '6056789012', 'contacto@papeleriamoderna.com', 'Av. Comercial 123, Medellín', 2, NULL, NULL, NULL),
(3, 3, 'Natural', 'Carlos Torres', '1122334455', '3101234567', 'carlos.torres@mail.com', 'Cra 10 #20-30, Cali', 3, NULL, NULL, NULL),
(4, 4, 'Jurídico', 'Muebles S.A.S', '2233445566', '3159876543', 'ventas@muebles.com', 'Zona Industrial 45, Medellín', 4, NULL, NULL, NULL),
(5, 5, 'Jurídico', 'Repuestos Industriales Ltda', '3344556677', '3009871234', 'contacto@repuestos.com', 'Av. 80 #45-67, Bogotá', 5, NULL, NULL, NULL);

INSERT INTO `producto` (`tipo_inventario`, `idprod`, `codprod`, `nomprod`, `desprod`, `idcat`, `idemp`, `unimed`, `stkmin`, `stkmax`, `imgprod`, `costouni`, `precioven`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 1, 'PROD-001', 'Laptop HP EliteBook', 'Laptop i7 16GB RAM 512GB SSD', 1, 1, 'UND', 5, 50, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 2, 'PROD-002', 'Taladro Inalámbrico', 'Taladro 20V con 2 baterías', 2, 1, 'UND', 10, 100, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 3, 'PROD-003', 'Resma Papel A4', 'Paquete 500 hojas 75g', 3, 2, 'UND', 20, 200, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 4, 'PROD-004', 'Silla Oficina', 'Silla ergonómica ejecutiva', 4, 3, 'UND', 5, 30, NULL, NULL, NULL, NULL, NULL, NULL),
(1, 5, 'PROD-005', 'Filtro de Aire', 'Filtro para maquinaria industrial', 5, 4, 'UND', 2, 15, NULL, NULL, NULL, NULL, NULL, NULL),
(0, 6, 'PROD-11', 'YUPI', 'Paquete de papas', 3, 1, 'kg', 1, 10, NULL, 1000.00, 8000.00, NULL, '2025-11-06 01:41:34', 1),
(0, 7, 'PROD-12', 'YOGURT', 'Alpina sabor a fresa', 3, 1, 'ml', 1, 545, NULL, 1000.00, 8000.00, NULL, '2025-11-06 01:28:12', 1);


INSERT INTO `inventario` (`idinv`, `idemp`, `idprod`, `idubi`, `cant`, `fec_crea`, `fec_actu`) VALUES
(1, 1, 1, 1, 15, NULL, NULL),
(2, 1, 2, 1, 30, NULL, NULL),
(3, 2, 3, 2, 150, NULL, NULL),
(4, 3, 4, 3, 12, NULL, NULL),
(5, 4, 5, 4, 5, NULL, NULL);

INSERT INTO `kardex` (`idkar`, `idemp`, `anio`, `mes`, `cerrado`, `fec_crea`, `fec_actu`) VALUES
(1, 1, 2024, 1, 1, NULL, NULL),
(2, 2, 2024, 1, 0, NULL, NULL),
(3, 3, 2024, 2, 0, NULL, NULL),
(4, 4, 2024, 2, 1, NULL, NULL),
(5, 5, 2024, 3, 0, NULL, NULL);

INSERT INTO `movim` (`idmov`, `idkar`, `idprod`, `idubi`, `fecmov`, `tipmov`, `cantmov`, `valmov`, `costprom`, `docref`, `obs`, `idusu`, `fec_crea`, `fec_actu`) VALUES
(1, 1, 1, 1, '2024-01-15', 1, 5, 2500.00, 500.00, 'FACT-001', NULL, 1, '2025-11-02 02:13:56', '2025-11-02 02:13:56'),
(2, 1, 2, 1, '2024-01-15', 1, 10, 2000.00, 200.00, 'FACT-001', NULL, 1, '2025-11-02 02:13:56', '2025-11-02 02:13:56'),
(3, 2, 3, 2, '2024-01-18', 1, 50, 180.50, 3.61, 'FACT-002', NULL, 1, '2025-11-02 02:13:56', '2025-11-02 02:13:56'),
(4, 3, 4, 3, '2024-02-05', 2, 3, 900.00, 300.00, 'FACT-003', NULL, 2, '2025-11-02 02:13:56', '2025-11-02 02:13:56'),
(5, 4, 5, 4, '2024-02-10', 1, 7, 1400.00, 200.00, 'FACT-004', NULL, 3, '2025-11-02 02:13:56', '2025-11-02 02:13:56');


INSERT INTO `modulo` (`idmod`, `nommod`, `icono`, `ruta`, `orden`, `fec_crea`, `fec_actu`, `act`) VALUES
(1, 'Principal', 'fa fa-layer-group', '#', 1, '2025-11-02 02:13:56', NULL, 1);

INSERT INTO `pagina` (`idpag`, `idmod`, `nompag`, `ruta`, `icono`, `orden`, `fec_crea`, `fec_actu`, `act`) VALUES
(1001, 1, 'Empresas', 'views/vemp.php', 'fa fa-building', 1, '2025-11-02 02:13:56', NULL, 1),
(1002, 1, 'Productos', 'views/vprod.php', 'fa fa-box', 2, '2025-11-02 02:13:56', NULL, 1),
(1003, 1, 'Proveedores', 'views/vprov.php', 'fa fa-truck', 3, '2025-11-02 02:13:56', NULL, 1),
(1004, 1, 'Empleados', 'views/vusemp.php', 'fa fa-users', 4, '2025-11-02 02:13:56', NULL, 1),
(1005, 1, 'Categorías', 'views/vcat.php', 'fa fa-tags', 5, '2025-11-02 02:13:56', NULL, 1),
(1006, 1, 'Auditoría', 'views/vaud.php', 'fa fa-shield', 6, '2025-11-02 02:13:56', NULL, 1),
(1007, 1, 'Kardex', 'views/vkard.php', 'fa fa-clipboard-list', 7, '2025-11-02 02:13:56', NULL, 1),
(1008, 1, 'Lotes', 'views/vlote.php', 'fa fa-layer-group', 8, '2025-11-02 02:13:56', NULL, 1),
(1009, 1, 'Inventario', 'views/vinv.php', 'fa fa-boxes', 9, '2025-11-02 02:13:56', NULL, 1),
(1010, 1, 'Movimientos', 'views/vmovim.php', 'fa fa-exchange-alt', 10, '2025-11-02 02:13:56', NULL, 1),
(1011, 1, 'Dominios', 'views/vdom.php', 'fa fa-database', 11, '2025-11-02 02:13:56', NULL, 1),
(1012, 1, 'Valores', 'views/vval.php', 'fa fa-check-circle', 12, '2025-11-02 02:13:56', NULL, 1),
(1013, 1, 'Solicitud Salida', 'views/vsolsal.php', 'fa fa-file-alt', 13, '2025-11-02 02:13:56', NULL, 1),
(1014, 1, 'Detalle salida', 'views/vdetsal.php', 'fa fa-file-alt', 14, '2025-11-02 02:13:56', NULL, 1),
(1015, 1, 'Solicitud entrada', 'views/vsoent.php', 'fa fa-file-alt', 15, '2025-11-02 02:13:56', NULL, 1),
(1016, 1, 'Modulo', 'views/vmod.php', 'fa fa-file-alt', 16, '2025-11-02 02:13:56', NULL, 1),
(1017, 1, 'Ubicacion', 'views/vubi.php', 'fa fa-map-marker-alt', 17, '2025-11-02 02:13:56', NULL, 1),
(1018, 1, 'Usuarios', 'views/vusu.php', 'fa fa-user-cog', 18, '2025-11-02 02:13:56', NULL, 1),
(1019, 1, 'Pagina', 'views/vpag.php', 'fa fa-user-cog', 19, '2025-11-02 02:13:56', NULL, 1),
(1020, 1, 'Perfil', 'views/vper.php', 'fa fa-user-cog', 20, '2025-11-02 02:13:56', NULL, 1);

INSERT INTO `pxp` (`idper`, `idpag`) VALUES
-- IDPER 1: SUPERADMIN (TODAS LAS 20 PÁGINAS) - SIN CAMBIOS
(1, 1001), (1, 1002), (1, 1003), (1, 1004), (1, 1005), (1, 1006), (1, 1007), (1, 1008), (1, 1009), (1, 1010),
(1, 1011), (1, 1012), (1, 1013), (1, 1014), (1, 1015), (1, 1016), (1, 1017), (1, 1018), (1, 1019), (1, 1020),

-- IDPER 2: ADMIN/EMPRESA (Gestión de la Empresa) - SIN CAMBIOS
(2, 1001),(2, 1002), (2, 1003), (2, 1004), (2, 1005), (2, 1006), (2, 1007), (2, 1008), (2, 1009), (2, 1010), (2, 1013),
(2, 1014), (2, 1015), (2, 1017),

-- IDPER 3: EMPLEADO (Operación Ampliada - Puede ver Productos, Categorías, Ubicación, Lotes, etc.)
(3, 1002), -- Productos
(3, 1003), -- Proveedores
(3, 1005), -- Categorías
(3, 1008), -- Lotes
(3, 1009), -- Inventario
(3, 1010), -- Movimientos
(3, 1013), -- Solicitud Salida
(3, 1015), -- Solicitud Entrada
(3, 1017); -- Ubicación

INSERT INTO `usuario_empresa` (`idusu`, `idemp`, `fec_crea`) VALUES
-- VÍNCULOS DE GERENTES (Admin/Empresa - idper 2)
(2, 1, '2025-11-25 22:00:01'), -- Juan P. -> Empresa 1
(6, 2, '2025-11-25 22:00:02'), -- Juliana -> Empresa 2
(7, 3, '2025-11-04 01:49:20'), -- Pedro R. -> Empresa 3
(3, 4, '2025-11-01 00:00:00'), -- 🔑 CORREGIDO: María G. -> Empresa 4
(4, 5, '2025-11-25 22:00:03'), -- Pedro R. -> Empresa 5

-- VÍNCULOS DE EMPLEADOS (idper 3)
(10, 3, '2025-11-20 20:51:44'), -- Empleado -> Empresa 3
(11, 3, '2025-11-20 20:53:45'), -- Empleado -> Empresa 3
(12, 3, '2025-11-20 20:55:51'), -- Empleado -> Empresa 3
(13, 3, '2025-11-21 07:08:29'), -- Empleado -> Empresa 3
(9, 4, '2025-11-19 23:31:26'),  -- Empleado -> Empresa 4
(5, 4, '2025-11-25 22:00:04'); -- Laura M. (Empleado Faltante) -> Empresa 4

-- Agregar idemp a la tabla dominio
ALTER TABLE dominio ADD COLUMN idemp INT(10) AFTER desdom;

-- Agregar idemp a la tabla valor (si también lo necesitas)
ALTER TABLE valor ADD COLUMN idemp INT(10) AFTER desval;

-- Crear índices para mejorar rendimiento
ALTER TABLE dominio ADD KEY fk_dom_idemp (idemp);
ALTER TABLE valor ADD KEY fk_val_idemp (idemp);

-- Agregar llaves foráneas
ALTER TABLE dominio ADD CONSTRAINT fk_dom_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp);
ALTER TABLE valor ADD CONSTRAINT fk_val_emp FOREIGN KEY (idemp) REFERENCES empresa(idemp);
