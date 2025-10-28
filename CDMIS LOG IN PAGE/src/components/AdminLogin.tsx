import { useState } from "react";
import { useNavigate } from "react-router-dom";
import backgroundImage from "figma:asset/5a957da2c117ca8b97f1cfc3c25ae862f8edc83b.png";
import bipsuLogo from "figma:asset/361443aa4e0a27fb2ccdaa4a85ae3fcb8a577692.png";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { useAuth } from "../contexts/AuthContext";

interface AdminLoginProps {
  onBack?: () => void;
}

export function AdminLogin({ onBack }: AdminLoginProps) {
  const [schoolId, setSchoolId] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setIsLoading(true);

    try {
      await login({ school_id: schoolId, password });
      // Redirect to admin dashboard
      navigate("/admin/reports");
    } catch (err: any) {
      setError(err.response?.data?.error || "Login failed. Please check your credentials.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="size-full flex">
      {/* Left side - Background Image */}
      <div className="flex-1 relative overflow-hidden">
        <img 
          src={backgroundImage} 
          alt="BIPSU Campus" 
          className="w-full h-full object-cover"
        />
      </div>

      {/* Right side - Login Panel */}
      <div className="w-full max-w-md bg-white flex flex-col items-center justify-center px-12 py-16">
        {/* BIPSU Logo */}
        <div className="mb-8">
          <img 
            src={bipsuLogo} 
            alt="BIPSU Logo" 
            className="w-32 h-32"
          />
        </div>

        {/* University Name */}
        <div className="text-center mb-8">
          <h1 className="text-[15px] leading-tight mb-1">
            Biliran Province State University
          </h1>
          <p className="text-[15px]">
            Naval, Biliran
          </p>
        </div>

        {/* Login Form */}
        <div className="w-full">
          {/* Administrator Label */}
          <div className="mb-6">
            <Button 
              className="w-full h-12 bg-[#6366F1] hover:bg-[#5558E3] text-white rounded-lg"
              disabled
            >
              Administrator
            </Button>
          </div>

          <form onSubmit={handleLogin} className="space-y-4">
            {error && (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {error}
              </div>
            )}
            
            {/* User ID Input */}
            <div>
              <label htmlFor="userId" className="block text-sm mb-2">
                School ID
              </label>
              <Input
                id="userId"
                type="text"
                value={schoolId}
                onChange={(e) => setSchoolId(e.target.value)}
                className="w-full h-12 bg-white border-b border-gray-300 rounded-none px-0 focus-visible:ring-0 focus-visible:border-gray-500"
                placeholder="e.g., 22-1-02642"
                required
              />
            </div>

            {/* Password Input */}
            <div>
              <label htmlFor="password" className="block text-sm mb-2">
                Password (4 characters)
              </label>
              <Input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full h-12 bg-white border-b border-gray-300 rounded-none px-0 focus-visible:ring-0 focus-visible:border-gray-500"
                placeholder=""
                maxLength={4}
                required
              />
            </div>

            {/* Login Button */}
            <div className="pt-4">
              <Button 
                type="submit"
                disabled={isLoading}
                className="w-full h-12 bg-[#6366F1] hover:bg-[#5558E3] text-white rounded-lg disabled:opacity-50"
              >
                {isLoading ? "Logging in..." : "Login"}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
