ALTER TABLE `Instances`
  ADD CONSTRAINT Instances_ibfk_1 FOREIGN KEY (idProfile) REFERENCES `Profiles` (idProfile);

ALTER TABLE `Profiles`
  ADD CONSTRAINT Profiles_ibfk_1 FOREIGN KEY (idCustomer) REFERENCES Customers (idCustomer);

ALTER TABLE `ProfileConfigData`
  ADD CONSTRAINT ProfileConfigData_ibfk_1 FOREIGN KEY (idProfile) REFERENCES `Profiles` (idProfile);