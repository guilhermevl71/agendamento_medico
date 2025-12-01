import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-cadastro',
  imports: [FormsModule, NgIf, RouterLink],
  templateUrl: './cadastro.component.html',
  styleUrls: ['./cadastro.component.css'] // <-- corrigido
})
export class CadastroComponent {

  constructor(
    private http: HttpClient,
    private router: Router
  ) {}

  nome = '';
  email = '';
  senha = '';
  cpf = '';
  convenio = '';
  erroMsg = '';
  isLoading: boolean = false;

  fazerCadastro() {
    this.isLoading = true;
    this.erroMsg = '';

    this.http.post<any>(
      'http://localhost:8000/api/register.php',
      {
        name: this.nome,
        email: this.email,
        password: this.senha,
        cpf: this.cpf,
        convenio: this.convenio
      }
    ).subscribe({
      next: (res) => {
        this.isLoading = false;

        // Se a API retornar success true, avisa e navega
        if (res && res.success) {
          alert('Cadastro concluído com sucesso!');
          this.router.navigate(['/login']);
        } else {
          // fallback
          alert('Cadastro concluído! Faça login.');
          this.router.navigate(['/login']);
        }
      },
      error: (err) => {
        this.isLoading = false;

        if (err.status === 400) {
          this.erroMsg = 'Preencha todos os campos obrigatórios.';
        } 
        else if (err.status === 409) {
          this.erroMsg = 'Email ou CPF já cadastrado.';
        }
        else {
          this.erroMsg = 'Erro ao cadastrar. Tente novamente.';
        }
      }
    });
  }
}
