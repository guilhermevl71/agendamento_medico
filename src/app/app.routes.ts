import { Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { CriarAgendamentoComponent } from './criar-agendamento/criar-agendamento.component';
import { loggedGuard } from './guards/logged.guard';
import { authGuard } from './guards/auth.guard';
import { MainComponent } from './main/main.component';
import { CadastroComponent } from './cadastro/cadastro.component';

export const routes: Routes = [
    {path: '', redirectTo: 'dashboard', pathMatch: 'full'},
    {path: 'login', component: LoginComponent, canActivate: [loggedGuard]},
    {path: 'cadastro', component: CadastroComponent, canActivate: [loggedGuard]},
    {path: 'agendamentos', component: DashboardComponent, canActivate: [authGuard]},
    {path: 'criar-agendamento', component: CriarAgendamentoComponent, canActivate: [authGuard]},
    {path: 'dashboard', component: MainComponent}
];
