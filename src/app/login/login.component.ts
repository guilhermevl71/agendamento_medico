import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router, RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-login',
  imports: [FormsModule, NgIf, RouterLink],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent {

  constructor(
    private http: HttpClient,
    private auth: AuthService,
    private router: Router
  ) {}

  name = '';
  senha = '';
  erroMsg = '';
  isLoading: boolean = false;

  fazerLogin() {
    this.isLoading = true;

    this.http.post<any>(
      'http://localhost:8000/api/login.php',
      { name: this.name, password: this.senha }
    ).subscribe({
      next: (res) => {
        this.auth.loginSetToken(res.token);
        sessionStorage.setItem('user_id', res.user_id);
        this.router.navigate(['/dashboard']);
        this.isLoading = false;
      },
      error: (err) => {
        if (err.status === 401) {
          this.erroMsg = 'Usuário ou senha incorretos.';
        } else if (err.status === 400) {
          this.erroMsg = 'Preencha todos os campos.';
        } else {
          this.erroMsg = 'Erro ao fazer login.';
        }
        this.isLoading = false;
      }
    });
  }
}
