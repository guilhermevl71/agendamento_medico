import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private KEY = 'token'; 

  loginSetToken(token: string) {
    sessionStorage.setItem(this.KEY, token);
  }

  getToken(): string | null {
    return sessionStorage.getItem(this.KEY);
  }

  logout() {
    sessionStorage.removeItem(this.KEY);
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }
}
