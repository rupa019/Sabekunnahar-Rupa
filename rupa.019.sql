USE meowwoof_simple;

CREATE TABLE IF NOT EXISTS animals (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    type          ENUM('dog','cat') NOT NULL,
    area_id       INT,
    health_status ENUM('healthy','sick','injured') DEFAULT 'healthy',
    is_vaccinated BOOLEAN DEFAULT FALSE,
    age           INT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL
);


CREATE TABLE IF NOT EXISTS adoption_requests (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    animal_id    INT NOT NULL,
    volunteer_id INT NOT NULL,
    status       ENUM('pending','approved','rejected') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id)    REFERENCES animals(id)    ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE
);



ALTER TABLE animals
ADD COLUMN weight DECIMAL(5,2) DEFAULT NULL;


ALTER TABLE animals
ADD INDEX idx_health_status (health_status);


ALTER TABLE animals
DROP INDEX idx_health_status;


INSERT INTO animals (name, type, area_id, health_status, is_vaccinated, age)
VALUES ('Snowy', 'cat', 2, 'healthy', FALSE, 1);

UPDATE animals
SET is_vaccinated = TRUE,
    health_status = 'healthy'
WHERE name = 'Snowy' AND area_id = 2;


UPDATE adoption_requests
SET status = 'approved'
WHERE animal_id = (SELECT id FROM animals WHERE name = 'Snowy' LIMIT 1)
  AND status = 'pending';


DELETE FROM animals
WHERE name = 'Snowy' AND area_id = 2;



SELECT
    a.id,
    a.name,
    a.type,
    a.age,
    a.health_status,
    CASE WHEN a.is_vaccinated THEN 'Yes' ELSE 'No' END AS vaccinated,
    ar.name     AS area,
    ar.location
FROM animals a
LEFT JOIN areas ar ON a.area_id = ar.id
ORDER BY ar.name, a.type;

SELECT
    ar.id            AS request_id,
    an.name          AS animal_name,
    an.type,
    vol.name         AS requested_by,
    vol.phone,
    ar.status,
    ar.request_date
FROM adoption_requests ar
JOIN animals    an  ON ar.animal_id    = an.id
JOIN volunteers vol ON ar.volunteer_id = vol.id
WHERE ar.status = 'pending'
ORDER BY ar.request_date;

SELECT
    ar.name         AS area,
    a.name          AS heaviest_animal,
    a.type,
    a.weight        AS weight_kg
FROM animals a
JOIN areas ar ON a.area_id = ar.id
WHERE a.weight = (
    SELECT MAX(a2.weight)
    FROM animals a2
    WHERE a2.area_id = a.area_id
)
ORDER BY a.weight DESC;

SELECT
    ar.name                           AS area,
    COUNT(a.id)                       AS total_animals,
    SUM(a.health_status = 'healthy')  AS healthy,
    SUM(a.health_status = 'sick')     AS sick,
    SUM(a.health_status = 'injured')  AS injured,
    SUM(a.is_vaccinated = TRUE)       AS vaccinated
FROM areas ar
LEFT JOIN animals a ON ar.id = a.area_id
GROUP BY ar.id, ar.name
ORDER BY total_animals DESC;
