<?php

/*********************************************************************************
 * TEMS is a Time and Expense Management program developed by
 * Initechs, LLC. Copyright (C) 2009 - 2013 Initechs LLC.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY INITECHS, INITECHS DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Initechs headquarters at 1841 Piedmont Road, Suite 301,
 * Marietta, GA, USA. or at email address contact@initechs.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display od the "Initechs" logo.
 * If the display of the logo is not reasonably feasible for technical reasons,
 * the Appropriate Legal Notices must display the words "Powered by Initechs".

 ********************************************************************************/

$preparation = array (
"ALTER TABLE users
	DROP FOREIGN KEY fk_users_users1",
);

$finalize = array (
"ALTER TABLE users
			ADD CONSTRAINT fk_users_users1
			FOREIGN KEY (reportto)
			REFERENCES users (users_id)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION",
);


$allTables = array (
10 => "expensedetails",
20 => "expenseheaders",
30 => "times",
40 => "invoiceheaders",
50 => "projects_users_tasks",
60 => "projects_users",
70 => "projects",
80 => "tasks",
90 => "expensecategories",
100 => "authorizations",
110 => "authorizationlists",
120 => "accounts",
130 => "users",
140 => "company",
);



$changedTables = array (

10 => array('company' => "select 
uid,
company_id,
name,
address1,
address2,
city,
state,
postalcode,
country,
logo,
weekendday,
language,
createat,
createby,
changeat,
changeby
from company"),

20 => array('authorizationlists' => "select
uid,
authorizations_id,
description,
createat,
createby,
changeat,
changeby
from authorizationlists"),

30 => array('authorizations' => "select
uid,
authorizations_id,
module,
action,
authlevel,
createat,
createby,
changeat,
changeby
from authorizations"),

40 => array('tasks' => "select
uid,
tasks_id,
name,
description,
status,
createat,
createby,
changeat,
changeby
from tasks"),

50 => array('expensecategories' => "select 
uid,
expensecategories_id,
description,
seq,
ismileage,
mileagerate,
status,
createat,
createby,
changeat,
changeby
from expensecategories"),

60 => array('users' => "select 
uid,
users_id,
fullname,
password,
email,
joindate,
reportto,
title,
status,
createat,
createby,
changeat,
changeby,
authorizations_id,
usergroup,
dateformat,
language
from users"),

70 => array('accounts' => "SELECT 
uid,
accounts_id,
name,
address1,
address2,
city,
state,
postalcode,
country,
contact,
email,
lastbilldate,
status,
createat,
createby,
changeat,
changeby
FROM accounts"),

80 => array('projects' => "SELECT
 p.uid as uid,
 p.projects_id as projects_id,
 p.name as name,
 p.description as description,
 p.accounts_id as accounts_id,
 p.billtoaccount_id as billtoaccounts_id,
 a.billcycle as billcycle,
 a.lastbilldate as lastbilldate,
 p.status as status,
 p.createat as createat,
 p.createby as createby,
 p.changeat as changeat,
 p.changeby as changeby
 FROM projects p, accounts a
 WHERE p.billtoaccount_id = a.accounts_id"),

90 => array('projects_users' => "SELECT
 upr.users_id as users_id,
 upr.projects_id as projects_id,
 pr.effective_date as effective_date,
 pr.rate as rate,
 r.description as role,
 upr.status as status,
 upr.createat as createat,
 upr.createby as createby,
 upr.changeat as changeat,
 upr.changeby as changeby
 FROM users_projects_roles upr, projects_roles pr, roles r
 WHERE upr.projects_id = pr.projects_id
   and upr.roles_id = pr.roles_id
   and pr.roles_id = r.roles_id"),

100 => array('projects_users_tasks' => "SELECT
 upr.projects_id as projects_id,
 upr.users_id as users_id,
 pt.tasks_id as tasks_id,
 pr.effective_date as effective_date,
 upr.status as status,
 upr.createat as createat,
 upr.createby as createby,
 upr.changeat as changeat,
 upr.changeby as changeby
 FROM users_projects_roles upr, projects_roles pr, projects_tasks pt
 WHERE upr.projects_id = pr.projects_id
   and upr.roles_id = pr.roles_id
   and upr.projects_id = pt.projects_id"),

110 => array('invoiceheaders' => "SELECT
 i.invoices_id as invoices_id,
 p.project as projects_id,
 i.begindate as begindate,
 i.enddate as enddate,
 i.invoicedate as invoicedate,
 i.status as status,
 i.createby as createby,
 i.createat as createat,
 i.changeby as changeby,
 i.changeat as changeat
 
FROM invoiceheaders i 
inner join 
(select distinct p1.billtoaccount_id, max(p1.projects_id) as project from projects p1
    group by p1.billtoaccount_id) p
on i.accounts_id = p.billtoaccount_id
where i.invoices_id not like '@OpenInv%'"),

120 => array('invoiceheaders' => "SELECT
 concat('@OpenInv', p.projects_id) as invoices_id,
 p.projects_id as projects_id,
 i.begindate as begindate,
 i.enddate as enddate,
 i.invoicedate as invoicedate,
 i.status as status,
 i.createby as createby,
 i.createat as createat,
 i.changeby as changeby,
 i.changeat as changeat
 
FROM invoiceheaders i 
inner join 
 projects p
on i.accounts_id = p.billtoaccount_id
where i.invoices_id like '@OpenInv%'"),

130 => array('times' => "select 
t.uid as uid,
t.weekenddate as weekenddate,
t.workdate as workdate,
t.description as description,
t.comments as comments,
t.location as location,
t.nonbillablehours as nonbillablehours,
t.billablehours as billablehours,
t.status as status,
t.submitdate as submitdate,
t.approvedate as approvedate,
t.rate as rate,
t.createby as createby,
t.createat as createat,
t.changeby as changeby,
t.changeat as changeat,
t.users_id as users_id,
t.projects_id as projects_id,
t.tasks_id as tasks_id,
if (t.invoices_id like '@OpenInv%', concat('@OpenInv', t.projects_id), t.invoices_id) as invoices_id
from times t"),

140 => array('expenseheaders' => "select
e.uid as uid,
e.weekenddate as weekenddate,
e.description as description,
e.comment as comment,
e.location as location,
e.status as status,
e.submitdate as submitdate,
e.approvedate as approvedate,
e.invoicedate as invoicedate,
e.createby as createby,
e.createat as createat,
e.changeby as changeby,
e.changeat as changeat,
e.users_id as users_id,
e.projects_id as projects_id,
if (e.invoices_id like '@OpenInv%', concat('@OpenInv', e.projects_id), e.invoices_id) as invoices_id
from expenseheaders e"),

150 => array('expensedetails' => "select 
e.uid as uid,
e.users_id as users_id,
e.projects_id as projects_id,
e.weekenddate as weekenddate,
e.expensedate as expensedate,
e.expensecategories_id as expensecategories_id,
e.description as description,
e.amount as amount,
if (e.invoices_id like '@OpenInv%', concat('@OpenInv', e.projects_id), e.invoices_id) as invoices_id,
e.mile as mile,
e.comment as comment,
e.createby as createby,
e.createat as createat,
e.changeby as changeby,
e.changeat as changeat
from expensedetails e"),

);


?>